@extends('behin-layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <input type="hidden" name="caseId" id="caseId" value="{{ $requestRow->id }}">
                    <h4 class="mb-0 fw-bold text-primary">جزئیات درخواست شماره پرونده {{ $requestRow->case_number ?? '---' }}</h4>
                    <a href="{{ route('simpleWorkflowReport.all-requests.index') }}" class="btn btn-light border-primary text-primary">
                        بازگشت به فهرست
                    </a>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="bg-gradient" style="background: linear-gradient(135deg, #1976d2, #42a5f5);">
                        <div class="p-4 text-white">
                            <h5 class="mb-1">{{ $requestRow->customer_name ?? 'کاربر ناشناخته' }}</h5>
                            <p class="mb-0 opacity-75">آخرین وضعیت: {{ $requestRow->last_status ?? '---' }}</p>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <input type="hidden" id="caseId" value="{{ $requestRow->id ?? '' }}">
                        <div class="row g-4">
                            @php
                                $details = [
                                    ['label' => 'شماره پرونده', 'value' => $requestRow->case_number, 'ltr' => true],
                                    ['label' => 'نام مشتری', 'value' => $requestRow->customer_name],
                                    ['label' => 'موبایل مشتری', 'value' => $requestRow->customer_mobile, 'ltr' => true],
                                    ['label' => 'نام دستگاه', 'value' => $requestRow->device_name],
                                    ['label' => 'سریال دستگاه', 'value' => $requestRow->device_serial, 'ltr' => true],
                                    ['label' => 'شماره پلاک دستگاه', 'value' => $requestRow->device_plaque, 'ltr' => true],
                                    ['label' => 'نوع تعمیر', 'value' => $requestRow->repair_type],
                                    ['label' => 'جزئیات نوع تعمیر', 'value' => $requestRow->repair_subtype],
                                    ['label' => 'تعمیرکار', 'value' => $requestRow->repairman],
                                    ['label' => 'تاریخ شروع تعمیر', 'value' => $requestRow->repair_start_at],
                                    ['label' => 'تاریخ پایان تعمیر', 'value' => $requestRow->repair_end_at],
                                    ['label' => 'تایید اول تعمیرات', 'value' => $requestRow->approval_first],
                                    ['label' => 'تایید دوم تعمیرات', 'value' => $requestRow->approval_second],
                                    ['label' => 'تایید سوم تعمیرات', 'value' => $requestRow->approval_third],
                                    ['label' => 'دستیاران تعمیر', 'value' => $requestRow->assistants],
                                    ['label' => 'هزینه تعیین شده', 'value' => $requestRow->repair_cost_formatted, 'ltr' => true],
                                    ['label' => 'هزینه‌های دریافت شده', 'value' => $requestRow->received_cost_formatted, 'ltr' => true],
                                ];
                            @endphp

                            @foreach($details as $detail)
                                <div class="col-md-6">
                                    <div class="bg-white rounded-4 shadow-sm h-100 p-3 d-flex flex-column gap-2 border border-light">
                                        <span class="text-secondary small fw-semibold">{{ $detail['label'] }}</span>
                                        <span class="fw-bold text-dark" @if(($detail['ltr'] ?? false)) dir="ltr" @endif>
                                            {{ $detail['value'] ?? '---' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($conversationViewModel)
                            @php
                                $conversationColumns = collect(explode(',', $conversationViewModel->default_fields ?? ''))
                                    ->map(fn ($column) => trim($column))
                                    ->filter()
                                    ->values();
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4 mt-4">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <h5 class="mb-0 fw-bold text-primary">تاریخچه مکالمات</h5>
                                    <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-1"
                                            onclick="get_view_model_rows('{{ $conversationViewModel->id }}', '{{ $conversationViewModel->api_key }}')">
                                        <span class="material-icons">refresh</span>
                                        بروزرسانی
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle" id="{{ $conversationViewModel->id }}">
                                            @if($conversationViewModel->show_as === 'table' && $conversationColumns->isNotEmpty())
                                                <thead class="table-light">
                                                    <tr>
                                                        @foreach($conversationColumns as $column)
                                                            <th>{{ trans('fields.' . $column) }}</th>
                                                        @endforeach
                                                        <th class="text-center">{{ trans('fields.Action') }}</th>
                                                    </tr>
                                                </thead>
                                            @endif
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    get_view_model_rows('{{ $conversationViewModel->id }}', '{{ $conversationViewModel->api_key }}');
                                });
                            </script>
                        @endif
                        @php
                            $callRecords = $callRecords ?? collect();
                            $callRecordsError = $callRecordsError ?? null;
                            $callRecordsSearchedNumbers = $callRecordsSearchedNumbers ?? [];
                            $directionLabels = [
                                'inbound' => 'ورودی',
                                'outbound' => 'خروجی',
                                'unknown' => 'نامشخص',
                            ];
                        @endphp

                        <div class="card border-0 shadow-sm rounded-4 mt-4">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-0 fw-bold text-primary">تاریخچه مکالمات تلفنی (AMI)</h5>
                                    @if(!empty($callRecordsSearchedNumbers))
                                        <span class="text-muted small">جستجو بر اساس: {{ implode('، ', $callRecordsSearchedNumbers) }}</span>
                                    @elseif(!empty($requestRow->customer_mobile))
                                        <span class="text-muted small">شماره جستجو: {{ $requestRow->customer_mobile }}</span>
                                    @else
                                        <span class="text-muted small">شماره تماسی برای این درخواست ثبت نشده است.</span>
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                @if($callRecordsError)
                                    <div class="alert alert-warning" role="alert">
                                        {{ $callRecordsError }}
                                    </div>
                                @endif

                                @if($callRecords->isEmpty())
                                    <p class="text-muted mb-0 text-center">مکالمه‌ای برای نمایش یافت نشد.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>تاریخ و زمان</th>
                                                    <th>نوع مکالمه</th>
                                                    <th>مدت</th>
                                                    <th>وضعیت</th>
                                                    <th class="text-center">فایل مکالمه</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($callRecords as $record)
                                                    <tr>
                                                        <td>
                                                            @if(!empty($record['started_at']))
                                                                {{ $record['started_at']->format('Y/m/d H:i') }}
                                                            @else
                                                                ---
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $directionLabels[$record['direction']] ?? $record['direction'] }}</span>
                                                            <div class="small text-muted mt-1">{{ $record['counterparty'] ?? '---' }}</div>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold">{{ $record['duration_human'] ?? '00:00' }}</span>
                                                            @if(!empty($record['duration_seconds']))
                                                                <div class="small text-muted">{{ $record['duration_seconds'] }} ثانیه</div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark border">{{ $record['status'] ?? '---' }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if(!empty($record['recording']['available']) && !empty($record['recording']['download_url']))
                                                                <div class="d-flex flex-column gap-2 align-items-center">
                                                                    <a class="btn btn-sm btn-outline-primary" href="{{ $record['recording']['download_url'] }}">
                                                                        دانلود فایل
                                                                    </a>
                                                                    @if(!empty($record['recording']['stream_url']))
                                                                        <audio controls preload="none" class="w-100">
                                                                            <source src="{{ $record['recording']['stream_url'] }}">
                                                                            مرورگر شما از پخش صوت پشتیبانی نمی‌کند.
                                                                        </audio>
                                                                    @endif
                                                                </div>
                                                            @else
                                                                <span class="text-muted">فاقد فایل</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
