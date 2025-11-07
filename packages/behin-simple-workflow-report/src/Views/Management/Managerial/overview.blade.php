@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش‌های مدیریتی ترکیبی')
@section('subtitle', 'تحلیل عملکرد واحدها، درآمد و مشتریان کلیدی')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;

        $unitPerformance = DB::table('wf_cases as cases')
            ->leftJoin('users', 'users.id', '=', 'cases.creator')
            ->leftJoin('wf_entity_transactions as transactions', 'transactions.case_id', '=', 'cases.id')
            ->select(
                'cases.creator as user_id',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT cases.id) as total_cases'),
                DB::raw('SUM(CASE WHEN transactions.transaction_type = "income" THEN CAST(REPLACE(transactions.amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN transactions.transaction_type = "expense" THEN CAST(REPLACE(transactions.amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) as total_expense')
            )
            ->groupBy('cases.creator', 'users.name')
            ->orderByDesc(DB::raw('COUNT(DISTINCT cases.id)'))
            ->limit(20)
            ->get();

        $caseClosureTime = DB::table('wf_cases')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_case_seconds')
            ->first();

        $taskClosure = DB::table('wf_inbox')
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->where('status', 'done')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, updated_at)) as avg_task_seconds')
            ->first();

        $avgCaseClosure = $caseClosureTime && $caseClosureTime->avg_case_seconds ? gmdate('H:i:s', (int) round($caseClosureTime->avg_case_seconds)) : '00:00:00';
        $avgTaskDuration = $taskClosure && $taskClosure->avg_task_seconds ? gmdate('H:i:s', (int) round($taskClosure->avg_task_seconds)) : '00:00:00';

        $monthlyRevenue = DB::table('wf_entity_transactions')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_label')
            ->selectRaw('SUM(CASE WHEN transaction_type = "income" THEN CAST(REPLACE(amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) as total_income')
            ->selectRaw('SUM(CASE WHEN transaction_type = "expense" THEN CAST(REPLACE(amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) as total_expense')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month_label')
            ->limit(24)
            ->get();

        $monthlyRepairs = DB::table('wf_entity_device_repair')
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_label')
            ->selectRaw('COUNT(*) as total_repairs')
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month_label')
            ->limit(24)
            ->get();

        $keyCustomers = DB::table('wf_entity_customers as customers')
            ->leftJoin('wf_entity_case_customer as cc', 'cc.customer_id', '=', 'customers.id')
            ->leftJoin('wf_entity_transactions as t', 't.case_id', '=', 'cc.case_id')
            ->select(
                'customers.id',
                'customers.fullname',
                DB::raw('COUNT(DISTINCT cc.case_id) as total_cases'),
                DB::raw('SUM(CASE WHEN t.transaction_type = "income" THEN CAST(REPLACE(t.amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) as total_income')
            )
            ->groupBy('customers.id', 'customers.fullname')
            ->havingRaw('SUM(CASE WHEN t.transaction_type = "income" THEN CAST(REPLACE(t.amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END) > 0')
            ->orderByDesc(DB::raw('SUM(CASE WHEN t.transaction_type = "income" THEN CAST(REPLACE(t.amount, ",", "") AS DECIMAL(18,2)) ELSE 0 END)'))
            ->limit(20)
            ->get();

        $avgIncome = $unitPerformance->avg('total_income');
        $avgCases = $unitPerformance->avg('total_cases');
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین زمان بستن پرونده</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ $avgCaseClosure }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین زمان تکمیل تسک‌ها</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $avgTaskDuration }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">میانگین درآمد واحدها</p>
                <p class="mt-2 text-3xl font-bold text-amber-600">{{ number_format($avgIncome ?? 0) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">عملکرد واحدها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">واحد/کاربر</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">درآمد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">هزینه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تراز</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($unitPerformance as $unit)
                            @php
                                $balance = ($unit->total_income ?? 0) - ($unit->total_expense ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $unit->user_name ?? ('کاربر #' . $unit->user_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($unit->total_cases ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-emerald-600 font-semibold">{{ number_format($unit->total_income ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-rose-600 font-semibold">{{ number_format($unit->total_expense ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm {{ $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">{{ number_format($balance) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای موجود نیست.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">روند درآمد ماهانه</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">ماه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">درآمد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">هزینه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تراز</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($monthlyRevenue as $row)
                            @php
                                $balance = ($row->total_income ?? 0) - ($row->total_expense ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->month_label ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-emerald-600 font-semibold">{{ number_format($row->total_income ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-rose-600 font-semibold">{{ number_format($row->total_expense ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm {{ $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">{{ number_format($balance) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای موجود نیست.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">مقایسه تعداد تعمیرات ماهانه</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">ماه</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد تعمیر</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($monthlyRepairs as $row)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $row->month_label ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->total_repairs ?? 0) }}</td>
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
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">مشتریان کلیدی</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مشتری</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد پرونده</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع درآمد</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($keyCustomers as $customer)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $customer->fullname ?? ('مشتری #' . $customer->id) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($customer->total_cases ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-emerald-600 font-semibold">{{ number_format($customer->total_income ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-slate-500">مشتری کلیدی شناسایی نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
