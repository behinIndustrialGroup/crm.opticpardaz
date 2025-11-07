@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش‌های مالی و حسابداری')
@section('subtitle', 'نمای کلی از فاکتورها، درآمدها، هزینه‌ها و تراز پرونده‌ها')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Carbon;

        $preInvoices = DB::table('wf_entity_pre_invoices')
            ->select('id', 'case_id', 'invoice_number', 'status', 'total_amount', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $invoiceItems = DB::table('wf_entity_pre_invoice_items')
            ->select('pre_invoice_id', 'title', 'quantity', 'unit_price', DB::raw('quantity * unit_price as line_total'))
            ->orderByDesc('pre_invoice_id')
            ->limit(200)
            ->get();

        $repairIncomes = DB::table('wf_entity_repair_incomes')
            ->select('id', 'case_id', 'amount', 'paid_at', 'payment_method')
            ->orderByDesc('paid_at')
            ->limit(100)
            ->get();

        $repairCosts = DB::table('wf_entity_repair_cost')
            ->select('id', 'case_id', 'title', 'amount', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $unexpectedCosts = DB::table('wf_entity_unexpected_costs')
            ->select('id', 'case_id', 'title', 'amount', 'created_at')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $transactions = DB::table('wf_entity_transactions')
            ->select('id', 'case_id', 'amount', 'type', 'category', 'payment_method', 'created_by', 'created_at')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        $transactionBreakdown = DB::table('wf_entity_transactions')
            ->select('payment_method', 'category', 'created_by', DB::raw('COUNT(*) as total'), DB::raw('SUM(amount) as sum_amount'))
            ->groupBy('payment_method', 'category', 'created_by')
            ->orderByDesc('sum_amount')
            ->limit(100)
            ->get();

        $caseFinancials = DB::table('wf_entity_transactions as t')
            ->select(
                't.case_id',
                DB::raw('SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END) as total_income'),
                DB::raw('SUM(CASE WHEN t.type = "expense" THEN t.amount ELSE 0 END) as total_expense')
            )
            ->groupBy('t.case_id')
            ->orderByDesc(DB::raw('SUM(CASE WHEN t.type = "income" THEN t.amount ELSE 0 END)'))
            ->limit(100)
            ->get();

        $users = DB::table('users')->whereIn('id', $transactions->pluck('created_by')->filter())->pluck('name', 'id');
    @endphp

    <div class="grid gap-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">تعداد فاکتورها و پیش‌فاکتورها</p>
                <p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($preInvoices->count()) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">درآمدهای ثبت‌شده</p>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ number_format($repairIncomes->sum('amount'), 0) }}</p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <p class="text-sm text-slate-500">هزینه‌های ثبت‌شده</p>
                <p class="mt-2 text-3xl font-bold text-rose-600">{{ number_format($repairCosts->sum('amount') + $unexpectedCosts->sum('amount'), 0) }}</p>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">فهرست فاکتورها و پیش‌فاکتورها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شماره</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مبلغ کل</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($preInvoices as $invoice)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $invoice->invoice_number ?? ('#' . $invoice->id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $invoice->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $invoice->status ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($invoice->total_amount ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $invoice->created_at ? Carbon::parse($invoice->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">فاکتوری ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">جزئیات اقلام فاکتور</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شناسه فاکتور</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شرح کالا/خدمت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">قیمت واحد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($invoiceItems as $item)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $item->pre_invoice_id }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $item->title ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->quantity ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($item->unit_price ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($item->line_total ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">آیتمی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">درآمدهای ثبت‌شده</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مبلغ</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">روش پرداخت</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @forelse($repairIncomes as $income)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $income->case_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($income->amount ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $income->payment_method ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $income->paid_at ? Carbon::parse($income->paid_at)->format('Y-m-d') : '---' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">درآمدی ثبت نشده است.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-slate-900 mb-4">هزینه‌های تعمیرات و پیش‌بینی‌نشده</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">عنوان</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مبلغ</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($repairCosts as $cost)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-600">هزینه تعمیر</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $cost->case_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $cost->title ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($cost->amount ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $cost->created_at ? Carbon::parse($cost->created_at)->format('Y-m-d') : '---' }}</td>
                                </tr>
                            @endforeach
                            @foreach($unexpectedCosts as $cost)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-slate-600">هزینه پیش‌بینی‌نشده</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $cost->case_id ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-700">{{ $cost->title ?? '---' }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($cost->amount ?? 0) }}</td>
                                    <td class="px-4 py-2 text-sm text-slate-600">{{ $cost->created_at ? Carbon::parse($cost->created_at)->format('Y-m-d') : '---' }}</td>
                                </tr>
                            @endforeach
                            @if($repairCosts->isEmpty() && $unexpectedCosts->isEmpty())
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">هزینه‌ای ثبت نشده است.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">تراز تراکنش‌های مالی</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نوع</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">دسته‌بندی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">روش پرداخت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">ثبت‌کننده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مبلغ</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $transaction->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $transaction->type ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $transaction->category ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $transaction->payment_method ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $users[$transaction->created_by] ?? ('کاربر #' . $transaction->created_by) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($transaction->amount ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $transaction->created_at ? Carbon::parse($transaction->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">تراکنشی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">تفکیک تراکنش‌ها</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">روش پرداخت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">دسته‌بندی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کاربر ثبت‌کننده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع مبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($transactionBreakdown as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->payment_method ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->category ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $users[$row->created_by] ?? ('کاربر #' . $row->created_by) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ number_format($row->total) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($row->sum_amount ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">تفکیکی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">مقایسه درآمد و هزینه به تفکیک پرونده</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع درآمد</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">جمع هزینه</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تراز نهایی</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($caseFinancials as $case)
                            @php
                                $balance = ($case->total_income ?? 0) - ($case->total_expense ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $case->case_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-emerald-600 font-semibold">{{ number_format($case->total_income ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm text-rose-600 font-semibold">{{ number_format($case->total_expense ?? 0) }}</td>
                                <td class="px-4 py-2 text-sm {{ $balance >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-bold">{{ number_format($balance) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای برای محاسبه تراز وجود ندارد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
