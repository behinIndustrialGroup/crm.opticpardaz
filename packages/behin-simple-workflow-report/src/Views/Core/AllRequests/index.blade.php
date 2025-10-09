@extends('behin-layouts.app')

@section('title', 'لیست تمام درخواست‌ها')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0">لیست تمام درخواست‌ها</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-light text-primary">{{ number_format($rows->total()) }} مورد</span>
                            <form method="GET" action="{{ route('simpleWorkflowReport.all-requests.export') }}">
                                @foreach(($filters ?? []) as $key => $value)
                                    @continue($value === null || $value === '')
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <button type="submit" class="btn btn-sm btn-light text-primary fw-semibold">
                                    خروجی اکسل
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $filters = $filters ?? [];
                            $hasActiveFilters = collect($filters)->except(['per_page'])->filter(fn($value) => $value !== null && $value !== '')->isNotEmpty();
                        @endphp

                        <div class="mb-3">
                            <button class="btn btn-outline-primary" type="button" data-toggle="collapse" data-target="#advanced-filters" aria-expanded="{{ $hasActiveFilters ? 'true' : 'false' }}" aria-controls="advanced-filters">
                                فیلتر پیشرفته
                            </button>
                        </div>

                        <div class="collapse {{ $hasActiveFilters ? 'show' : '' }}" id="advanced-filters">
                            <div class="card card-body border-0 shadow-sm mb-4">
                                <form method="GET" action="{{ route('simpleWorkflowReport.all-requests.index') }}">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">شماره پرونده</label>
                                            <input type="text" name="case_number" value="{{ $filters['case_number'] ?? '' }}" class="form-control" placeholder="مثال: 1234">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نام</label>
                                            <input type="text" name="user_firstname" value="{{ $filters['user_firstname'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نام خانوادگی</label>
                                            <input type="text" name="user_lastname" value="{{ $filters['user_lastname'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">شناسه قبض برق</label>
                                            <input type="text" name="electricity_bill_id" value="{{ $filters['electricity_bill_id'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نوع نیروگاه</label>
                                            <input type="text" name="powerhouse_type" value="{{ $filters['powerhouse_type'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">استان محل نیروگاه</label>
                                            <input type="text" name="powerhouse_province" value="{{ $filters['powerhouse_province'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">ظرفیت درخواستی</label>
                                            <input type="text" name="requested_capacity_of_powerhouse" value="{{ $filters['requested_capacity_of_powerhouse'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نتیجه اولین تماس</label>
                                            <input type="text" name="first_call_result" value="{{ $filters['first_call_result'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تمایل به دریافت وام</label>
                                            <input type="text" name="loan_interest" value="{{ $filters['loan_interest'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">مبلغ اولیه</label>
                                            <input type="text" name="initial_amount" value="{{ $filters['initial_amount'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">امکان‌سنجی</label>
                                            <input type="text" name="feasibility_study" value="{{ $filters['feasibility_study'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نتیجه تماس رابط مالی با متقاضی</label>
                                            <input type="text" name="fin_interface_call_result" value="{{ $filters['fin_interface_call_result'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">آخرین وضعیت درخواست</label>
                                            <input type="text" name="last_status" value="{{ $filters['last_status'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تعداد نمایش در هر صفحه</label>
                                            <select name="per_page" class="form-select">
                                                @foreach([10, 15, 25, 50, 100] as $size)
                                                    <option value="{{ $size }}" {{ ($perPage ?? 15) == $size ? 'selected' : '' }}>{{ $size }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <a href="{{ route('simpleWorkflowReport.all-requests.index') }}" class="btn btn-light">پاکسازی فیلتر</a>
                                        <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>شماره پرونده</th>
                                    <th>نام</th>
                                    <th>نام خانوادگی</th>
                                    <th>کدملی</th>
                                    <th>موبایل</th>
                                    <th>شناسه قبض برق</th>
                                    <th>نوع نیروگاه</th>
                                    <th>استان محل نیروگاه</th>
                                    <th>ظرفیت درخواستی</th>
                                    <th>نتیجه اولین تماس</th>
                                    <th>تمایل به دریافت وام</th>
                                    <th>مبلغ اولیه</th>
                                    <th>امکان‌سنجی</th>
                                    <th>نتیجه تماس رابط مالی با متقاضی</th>
                                    <th>آخرین وضعیت درخواست</th>
                                    <th class="text-center">جزئیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>{{ $row->number ?? '---' }}
                                            <a href="{{ route('simpleWorkflow.inbox.caseHistoryView', [ 'caseNumber' => $row->number ]) }}" target="_blank">
                                                <i class="material-icons">history</i>
                                            </a>
                                        </td>
                                        <td>{{ $row->user_firstname ?? '---' }}</td>
                                        <td>{{ $row->user_lastname ?? '---' }}</td>
                                        <td>{{ $row->user_national_id ?? '---' }}</td>
                                        <td>{{ $row->mobile ?? '---' }}</td>
                                        <td dir="ltr">{{ $row->electricity_bill_id ?? '---' }}</td>
                                        <td>{{ $row->powerhouse_type ?? '---' }}</td>
                                        <td>{{ $row->powerhouse_place_info_province ?? '---' }}</td>
                                        <td>{{ $row->requested_capacity_of_powerhouse ?? '---' }}</td>
                                        <td>{{ $row->first_call_result ?? '---' }}</td>
                                        <td>{{ $row->loan_interest ?? '---' }}</td>
                                        <td>{{ $row->initial_amount ?? '---' }}</td>
                                        <td>{{ $row->feasibility_study ?? '---' }}</td>
                                        <td>{{ $row->fin_interface_call_result ?? '---' }}</td>
                                        <td>
                                            @foreach ($row->last_status as $last_status)
                                                {{ $last_status->task->name ?? '' }}
                                            @endforeach
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('simpleWorkflowReport.all-requests.show', $row->number) }}" class="btn btn-sm btn-outline-primary px-3">
                                                مشاهده جزئیات
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center text-muted">رکوردی یافت نشد.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                            <div class="text-muted small">
                                نمایش {{ $rows->firstItem() ?? 0 }} تا {{ $rows->lastItem() ?? 0 }} از {{ number_format($rows->total()) }} رکورد
                            </div>
                            <div>
                                {{ $rows->onEachSide(1)->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
