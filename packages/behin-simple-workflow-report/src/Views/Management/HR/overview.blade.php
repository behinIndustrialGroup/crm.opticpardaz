@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش منابع انسانی و زمان')
@section('subtitle', 'مرخصی‌ها، تاییدکنندگان و تعطیلات رسمی')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;

        $timeoffs = DB::table('wf_entity_timeoffs')
            ->select('id', 'user_id', 'approver_id', 'status', 'from_date', 'to_date', 'days', 'reason', 'created_at')
            ->orderByDesc('from_date')
            ->limit(200)
            ->get();

        $timeoffTotalsByMonth = DB::table('wf_entity_timeoffs')
            ->selectRaw('DATE_FORMAT(from_date, "%Y-%m") as month_label')
            ->selectRaw('SUM(days) as total_days')
            ->groupBy(DB::raw('DATE_FORMAT(from_date, "%Y-%m")'))
            ->orderBy('month_label', 'desc')
            ->limit(12)
            ->get();

        $timeoffTotalsByUser = DB::table('wf_entity_timeoffs')
            ->select('user_id', DB::raw('SUM(days) as total_days'))
            ->groupBy('user_id')
            ->orderByDesc('total_days')
            ->limit(50)
            ->get();

        $approvers = DB::table('users')->whereIn('id', $timeoffs->pluck('approver_id')->filter())->pluck('name', 'id');
        $users = DB::table('users')->whereIn('id', $timeoffs->pluck('user_id')->merge($timeoffTotalsByUser->pluck('user_id'))->filter())->pluck('name', 'id');

        $holidayList = DB::table('wf_entity_holidays')
            ->select('id', 'title', 'date', 'description')
            ->orderByDesc('date')
            ->limit(50)
            ->get();
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">درخواست‌های مرخصی ثبت شده</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($timeoffs->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">جمع روزهای مرخصی سال جاری</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($timeoffs->where('from_date', '>=', now()->startOfYear())->sum('days')) }}</p>
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
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاییدکننده</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($timeoffs as $request)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $users[$request->user_id] ?? ('کاربر #' . $request->user_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $request->from_date ? Carbon::parse($request->from_date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $request->to_date ? Carbon::parse($request->to_date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($request->days ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $request->status ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $approvers[$request->approver_id] ?? ($request->approver_id ? 'کاربر #' . $request->approver_id : '---') }}</td>
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
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $users[$row->user_id] ?? ('کاربر #' . $row->user_id) }}</td>
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
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">عنوان</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">توضیحات</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($holidayList as $holiday)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $holiday->date ? Carbon::parse($holiday->date)->format('Y-m-d') : '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $holiday->title ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $holiday->description ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">تعطیلی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
