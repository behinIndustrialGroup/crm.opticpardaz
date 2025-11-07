@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش دستگاه‌ها و تعمیرات')
@section('subtitle', 'پایش وضعیت دستگاه‌ها، سوابق تعمیر و نرخ تأیید')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Str;

        $devices = DB::table('wf_entity_devices')
            ->select('id', 'case_id', 'case_number', 'name', 'brand', 'power', 'serial', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $repairs = DB::table('wf_entity_device_repair')
            ->select('id', 'device_id', 'case_id', 'repair_type', 'repair_start_date', 'repair_end_date', 'repair_is_approved', 'repair_is_approved_by', 'created_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $repairPictures = DB::table('wf_entity_device_repair_pictures')
            ->select('id', 'case_id', 'case_number', 'file', 'created_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $repairStats = DB::table('wf_entity_device_repair')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN IFNULL(repair_is_approved, "") <> "" THEN 1 ELSE 0 END) as approved')
            ->first();

        $averageDurationSeconds = $repairs->map(function ($repair) {
            if (empty($repair->repair_start_date) || empty($repair->repair_end_date)) {
                return null;
            }

            try {
                $start = Carbon::parse($repair->repair_start_date);
                $end = Carbon::parse($repair->repair_end_date);
                return max($end->diffInSeconds($start), 0);
            } catch (\Throwable $e) {
                return null;
            }
        })->filter()->average();

        $averageDuration = $averageDurationSeconds ? gmdate('H:i:s', (int) round($averageDurationSeconds)) : '00:00:00';
        $approvalRate = $repairStats && $repairStats->total ? round(($repairStats->approved / $repairStats->total) * 100, 2) : 0;

        $repairTypeDistribution = DB::table('wf_entity_device_repair')
            ->select('repair_type')
            ->get()
            ->map(function ($row) {
                $type = $row->repair_type ?? null;
                if (!$type) {
                    return 'نامشخص';
                }
                if (is_string($type) && Str::startsWith(trim($type), '{')) {
                    $decoded = json_decode($type, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return $decoded['title'] ?? ($decoded['name'] ?? 'نامشخص');
                    }
                }
                if (is_string($type)) {
                    return $type;
                }
                return json_encode($type, JSON_UNESCAPED_UNICODE);
            })
            ->groupBy(fn ($type) => $type)
            ->map(fn ($group) => $group->count())
            ->sortDesc();

        $deviceNames = $devices->pluck('name', 'id');
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد دستگاه‌های مشتریان</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($devices->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">نرخ تأیید تعمیرات</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $approvalRate }}<span class="text-base">%</span></p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین زمان تعمیر</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ $averageDuration }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">لیست دستگاه‌های مشتریان</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نام دستگاه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شماره سریال</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">برند</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ ثبت</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($devices as $device)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $device->name ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $device->serial ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $device->brand ?? ($device->power ? 'قدرت: ' . $device->power : '---') }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $device->case_number ?? $device->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $device->created_at ? Carbon::parse($device->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">دستگاهی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">سوابق تعمیر دستگاه‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">دستگاه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع تعمیر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شروع</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پایان</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تأیید</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($repairs as $repair)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $deviceNames[$repair->device_id] ?? ('دستگاه #' . $repair->device_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $repair->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ is_string($repair->repair_type) ? $repair->repair_type : (is_array($repair->repair_type ?? null) ? json_encode($repair->repair_type, JSON_UNESCAPED_UNICODE) : '---') }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $repair->repair_start_date ? Carbon::parse($repair->repair_start_date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $repair->repair_end_date ? Carbon::parse($repair->repair_end_date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $repair->repair_is_approved ? 'تأیید شده' : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">تعمیر ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">عکس‌های تعمیرات انجام شده</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شناسه تعمیر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">توضیحات</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مسیر فایل</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($repairPictures as $picture)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $picture->case_number ?? $picture->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">---</td>
                                <td class="px-4 py-2 text-sm text-slate-600">
                                    @if(!empty($picture->file))
                                        <a href="{{ url('storage/' . ltrim($picture->file, '/')) }}" class="text-blue-600 hover:text-blue-800" target="_blank">مشاهده</a>
                                    @else
                                        ---
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $picture->created_at ? Carbon::parse($picture->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">عکسی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">توزیع نوع خرابی‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع خرابی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($repairTypeDistribution as $type => $count)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $type }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($count) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعاتی موجود نیست.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
