@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش منابع انسانی و زمان')
@section('subtitle', 'مرخصی‌ها، تاییدکنندگان و تعطیلات رسمی')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;

        $timeoffs = DB::table('wf_entity_timeoffs')
            ->select('id', 'user', 'type', 'duration', 'approved', 'approved_by', 'start_timestamp', 'end_timestamp', 'description', 'created_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $timeoffTotalsByMonth = DB::table('wf_entity_timeoffs')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_label')
            ->selectRaw('SUM(duration) as total_days')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month_label', 'desc')
            ->limit(12)
            ->get();

        $timeoffTotalsByUser = DB::table('wf_entity_timeoffs')
            ->select('user', DB::raw('SUM(duration) as total_days'))
            ->groupBy('user')
            ->orderByDesc('total_days')
            ->limit(50)
            ->get();

        $approverIds = $timeoffs->pluck('approved_by')->filter(function ($value) {
            return !empty($value) && is_numeric($value);
        })->map(fn($value) => (int) $value);

        $userIds = $timeoffs->pluck('user')->merge($timeoffTotalsByUser->pluck('user'))->filter(function ($value) {
            return !empty($value) && is_numeric($value);
        })->map(fn($value) => (int) $value);

        $approvers = DB::table('users')->whereIn('id', $approverIds)->pluck('name', 'id');
        $users = DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');

        $holidayList = DB::table('wf_entity_holidays')
            ->select('id', 'date', 'description')
            ->orderByDesc('date')
            ->limit(50)
            ->get();

        $formatTimestamp = function ($value) {
            if (empty($value)) {
                return null;
            }

            try {
                if (is_numeric($value)) {
                    return Carbon::createFromTimestamp((int) $value);
                }

                return Carbon::parse($value);
            } catch (\Throwable $e) {
                return null;
            }
        };
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">درخواست‌های مرخصی ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($timeoffs->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                @php
                    $currentYearStart = now()->startOfYear();
                    $currentYearDays = $timeoffs->sum(function ($timeoff) use ($formatTimestamp, $currentYearStart) {
                        $start = $formatTimestamp($timeoff->start_timestamp) ?? $timeoff->created_at ? Carbon::parse($timeoff->created_at) : null;
                        return $start && $start->greaterThanOrEqualTo($currentYearStart) ? ($timeoff->duration ?? 0) : 0;
                    });
                @endphp
                <p class="text-sm text-slate-500">جمع روزهای مرخصی سال جاری</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($currentYearDays) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد تعطیلات ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($holidayList->count()) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">گزارش مرخصی‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کاربر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">از تاریخ</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تا تاریخ</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">روز</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاییدکننده</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($timeoffs as $request)
                            <tr>
                                @php
                                    $startDate = $formatTimestamp($request->start_timestamp) ?? ($request->created_at ? Carbon::parse($request->created_at) : null);
                                    $endDate = $formatTimestamp($request->end_timestamp);
                                    $statusLabel = $request->approved === 1 || $request->approved === '1' ? 'تأیید شده' : (($request->approved === 0 || $request->approved === '0') ? 'رد شده' : 'در انتظار');
                                @endphp
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $users[(int) $request->user] ?? ($request->user ? 'کاربر #' . $request->user : '---') }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $startDate ? $startDate->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $endDate ? $endDate->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($request->duration ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $request->type ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $statusLabel }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $approvers[(int) $request->approved_by] ?? ($request->approved_by ? 'کاربر #' . $request->approved_by : '---') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">درخواستی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">مجموع روزهای مرخصی به تفکیک ماه</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">ماه</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع روز</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($timeoffTotalsByMonth as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $row->month_label ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->total_days ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای موجود نیست.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">مجموع مرخصی به تفکیک کاربر</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کاربر</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع روز</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($timeoffTotalsByUser as $row)
                                <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $users[(int) $row->user] ?? ($row->user ? 'کاربر #' . $row->user : '---') }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->total_days ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعاتی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">تعطیلات رسمی ثبت شده</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">توضیحات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($holidayList as $holiday)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $holiday->date ? Carbon::parse($holiday->date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $holiday->description ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-sm text-slate-500">تعطیلی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
