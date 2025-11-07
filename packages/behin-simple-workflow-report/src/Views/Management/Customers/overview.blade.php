@extends('SimpleWorkflowReportView::Management.layouts.tailwind')

@section('title', 'گزارش مشتریان و پرونده‌ها')
@section('subtitle', 'مروری بر مشتریان ثبت‌شده، فعالیت پرونده‌ها و اطلاعات تکمیلی')

@section('content')
    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Support\Str;
        use Illuminate\Support\Carbon;

        $customers = DB::table('wf_entity_customers as customers')
            ->leftJoin('wf_entity_case_customer as case_customer', 'case_customer.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.fullname',
                'customers.mobile',
                'customers.national_id',
                DB::raw('MAX(case_customer.address) as address'),
                DB::raw('MAX(case_customer.postal_code) as postal_code'),
                DB::raw('MAX(case_customer.eco_number) as eco_number'),
                'customers.customer_job',
                DB::raw('MAX(customers.created_at) as created_at')
            )
            ->groupBy('customers.id', 'customers.fullname', 'customers.mobile', 'customers.national_id', 'customers.customer_job')
            ->orderByDesc(DB::raw('MAX(customers.created_at)'))
            ->limit(100)
            ->get();

        $caseDetails = DB::table('wf_entity_case_customer as c')
            ->leftJoin('wf_cases as cases', 'cases.id', '=', 'c.case_id')
            ->leftJoin('wf_entity_customers as customers', 'customers.id', '=', 'c.customer_id')
            ->select(
                'c.case_id',
                'c.case_number',
                'cases.status',
                'customers.fullname',
                'c.created_at',
                'c.postal_code',
                'c.eco_number'
            )
            ->orderByDesc('c.created_at')
            ->limit(100)
            ->get();

        $customerActivity = DB::table('wf_entity_customers as customers')
            ->leftJoin('wf_entity_case_customer as case_customer', 'case_customer.customer_id', '=', 'customers.id')
            ->leftJoin('wf_entity_pre_invoices as invoices', 'invoices.case_id', '=', 'case_customer.case_id')
            ->select(
                'customers.id',
                'customers.fullname',
                DB::raw('COUNT(DISTINCT case_customer.case_id) as total_cases'),
                DB::raw('COUNT(DISTINCT invoices.id) as total_invoices')
            )
            ->groupBy('customers.id', 'customers.fullname')
            ->orderByDesc(DB::raw('COUNT(DISTINCT case_customer.case_id) + COUNT(DISTINCT invoices.id)'))
            ->limit(50)
            ->get();

        $jobs = $customers->map(function ($customer) {
            $job = $customer->customer_job ?? null;
            if (empty($job)) {
                return null;
            }

            if (is_string($job) && Str::startsWith(trim($job), '{')) {
                $decoded = json_decode($job, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return [
                        'customer' => $customer->fullname,
                        'title' => $decoded['title'] ?? ($decoded['name'] ?? null),
                        'industry' => $decoded['industry'] ?? null,
                        'experience' => $decoded['experience'] ?? null,
                    ];
                }
            }

            return [
                'customer' => $customer->fullname,
                'title' => is_string($job) ? $job : json_encode($job, JSON_UNESCAPED_UNICODE),
                'industry' => null,
                'experience' => null,
            ];
        })->filter();
    @endphp

    <div class="grid gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">فهرست مشتریان ثبت‌شده</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">نام</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">موبایل</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کد ملی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">آدرس</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کدپستی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شماره اقتصادی</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($customers as $customer)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $customer->fullname ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $customer->mobile ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $customer->national_id ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $customer->address ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $customer->postal_code ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $customer->eco_number ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعاتی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">جزئیات پرونده مشتریان</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مشتری</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شماره پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">وضعیت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">کدپستی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شماره اقتصادی</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تاریخ ثبت</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($caseDetails as $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->fullname ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->case_number ?? ('#' . $row->case_id) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->status ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->postal_code ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->eco_number ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $row->created_at ? Carbon::parse($row->created_at)->format('Y-m-d') : '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">پرونده‌ای ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">میزان فعالیت مشتریان</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مشتری</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد پرونده</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">تعداد فاکتور</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">شاخص فعالیت</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($customerActivity as $row)
                            @php
                                $score = ($row->total_cases ?? 0) + ($row->total_invoices ?? 0);
                            @endphp
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $row->fullname ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($row->total_cases) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ number_format($row->total_invoices) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-900 font-semibold">{{ number_format($score) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">اطلاعاتی موجود نیست.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-xl font-semibold text-slate-900 mb-4">گزارش مشاغل مشتریان</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">مشتری</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">عنوان شغل</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">صنعت</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-slate-500 uppercase">سابقه</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-slate-200">
                        @forelse($jobs as $job)
                            <tr>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ $job['customer'] ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $job['title'] ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $job['industry'] ?? '---' }}</td>
                                <td class="px-4 py-2 text-sm text-slate-600">{{ $job['experience'] ?? '---' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-slate-500">داده‌ای برای مشاغل ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
