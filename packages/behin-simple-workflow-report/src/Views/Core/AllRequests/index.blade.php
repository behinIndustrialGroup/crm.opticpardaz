@extends('behin-layouts.app')

@php
    use Illuminate\Support\Str;
@endphp

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
                            $approvalOptions = [
                                '' => 'همه موارد',
                                'approved' => 'تایید شده',
                                'rejected' => 'رد شده',
                                'pending' => 'در انتظار',
                            ];
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
                                            <label class="form-label">نام مشتری</label>
                                            <input type="text" name="customer_name" value="{{ $filters['customer_name'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">موبایل مشتری</label>
                                            <input type="text" name="customer_mobile" value="{{ $filters['customer_mobile'] ?? '' }}" class="form-control" dir="ltr">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نام دستگاه</label>
                                            <input type="text" name="device_name" value="{{ $filters['device_name'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">سریال دستگاه</label>
                                            <input type="text" name="device_serial" value="{{ $filters['device_serial'] ?? '' }}" class="form-control" dir="ltr">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">نوع تعمیر</label>
                                            <input type="text" name="repair_type" value="{{ $filters['repair_type'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">جزئیات نوع تعمیر</label>
                                            <input type="text" name="repair_subtype" value="{{ $filters['repair_subtype'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تعمیرکار</label>
                                            <input type="text" name="repairman" value="{{ $filters['repairman'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تاریخ شروع از</label>
                                            <input type="date" name="repair_start_from" value="{{ $filters['repair_start_from'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تاریخ شروع تا</label>
                                            <input type="date" name="repair_start_to" value="{{ $filters['repair_start_to'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تاریخ پایان از</label>
                                            <input type="date" name="repair_end_from" value="{{ $filters['repair_end_from'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تاریخ پایان تا</label>
                                            <input type="date" name="repair_end_to" value="{{ $filters['repair_end_to'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تایید اول تعمیرات</label>
                                            <select name="approval_first" class="form-select">
                                                @foreach($approvalOptions as $key => $label)
                                                    <option value="{{ $key }}" {{ ($filters['approval_first'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تایید دوم تعمیرات</label>
                                            <select name="approval_second" class="form-select">
                                                @foreach($approvalOptions as $key => $label)
                                                    <option value="{{ $key }}" {{ ($filters['approval_second'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">تایید سوم تعمیرات</label>
                                            <select name="approval_third" class="form-select">
                                                @foreach($approvalOptions as $key => $label)
                                                    <option value="{{ $key }}" {{ ($filters['approval_third'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">حداقل هزینه تعیین شده</label>
                                            <input type="number" step="0.01" name="cost_min" value="{{ $filters['cost_min'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">حداکثر هزینه تعیین شده</label>
                                            <input type="number" step="0.01" name="cost_max" value="{{ $filters['cost_max'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">حداقل هزینه دریافت شده</label>
                                            <input type="number" step="0.01" name="income_min" value="{{ $filters['income_min'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">حداکثر هزینه دریافت شده</label>
                                            <input type="number" step="0.01" name="income_max" value="{{ $filters['income_max'] ?? '' }}" class="form-control">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">آخرین وضعیت</label>
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
                                    <th>نام مشتری</th>
                                    <th>موبایل مشتری</th>
                                    <th>نام دستگاه</th>
                                    <th>سریال دستگاه</th>
                                    <th>نوع تعمیر</th>
                                    <th>جزئیات نوع تعمیر</th>
                                    <th>تعمیرکار</th>
                                    <th>تاریخ شروع تعمیر</th>
                                    <th>تاریخ پایان تعمیر</th>
                                    <th>مدت تعمیر</th>
                                    <th>تایید اول تعمیرات</th>
                                    <th>تایید دوم تعمیرات</th>
                                    <th>تایید سوم تعمیرات</th>
                                    <th>دستیاران تعمیر</th>
                                    <th>هزینه تعیین شده</th>
                                    <th>هزینه‌های دریافت شده</th>
                                    <th>آخرین وضعیت</th>
                                    <th>گزارش تعمیرات</th>
                                    <th class="text-center">جزئیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            {{ $row->case_number ?? '---' }}
                                            @if(!empty($row->case_number))
                                                <a href="{{ route('simpleWorkflow.inbox.caseHistoryView', [ 'caseNumber' => $row->case_number ]) }}" target="_blank">
                                                    <i class="material-icons">history</i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $row->customer_name ?? '---' }}</td>
                                        <td dir="ltr">{{ $row->customer_mobile ?? '---' }}</td>
                                        <td>{{ $row->device_name ?? '---' }}</td>
                                        <td dir="ltr">{{ $row->device_serial ?? '---' }}</td>
                                        <td>{{ $row->repair_type ?: '---' }}</td>
                                        <td>{{ $row->repair_subtype ?: '---' }}</td>
                                        <td>{{ $row->repairman ?? '---' }}</td>
                                        <td>{{ $row->repair_start_at ?? '---' }}</td>
                                        <td>{{ $row->repair_end_at ?? '---' }}</td>
                                        <td>{{ $row->repair_duration ?? '---' }}</td>
                                        <td>{{ $row->approval_first ?? '---' }}</td>
                                        <td>{{ $row->approval_second ?? '---' }}</td>
                                        <td>{{ $row->approval_third ?? '---' }}</td>
                                        <td>{{ $row->assistants ?: '---' }}</td>
                                        <td>
                                            @if($row->repair_cost_formatted)
                                                {{ $row->repair_cost_formatted }}
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>
                                            @if($row->received_cost_formatted)
                                                {{ $row->received_cost_formatted }}
                                            @else
                                                ---
                                            @endif
                                        </td>
                                        <td>{{ $row->last_status ?? '---' }}</td>
                                        <td>{{ Str::limit($row->repair_report ?? '---', 100, '...') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('simpleWorkflowReport.all-requests.show', $row->case_number) }}" class="btn btn-sm btn-outline-primary px-3">
                                                مشاهده جزئیات
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="19" class="text-center text-muted">رکوردی یافت نشد.</td>
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
