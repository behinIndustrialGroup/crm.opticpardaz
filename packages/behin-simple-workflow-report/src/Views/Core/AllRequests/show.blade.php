@extends('behin-layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <input type="hidden" name="caseId" id="caseId" value="{{ $requestRow->id }}">
                    <h4 class="mb-0 fw-bold text-primary">جزئیات درخواست شماره پرونده {{ $requestRow->number ?? '---' }}</h4>
                    <a href="{{ route('simpleWorkflowReport.all-requests.index') }}" class="btn btn-light border-primary text-primary">
                        بازگشت به فهرست
                    </a>
                </div>

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="bg-gradient" style="background: linear-gradient(135deg, #1976d2, #42a5f5);">
                        <div class="p-4 text-white">
                            <h5 class="mb-1">{{ trim(($requestRow->user_firstname ?? '') . ' ' . ($requestRow->user_lastname ?? '')) ?: 'کاربر ناشناخته' }}</h5>
                            <p class="mb-0 opacity-75">آخرین وضعیت: {{ $requestRow->last_status ?? '---' }}</p>
                        </div>
                    </div>
                    <div class="card-body bg-light">
                        <input type="hidden" id="caseId" value="{{ $requestRow->case_id ?? '' }}">
                        <div class="row g-4">
                            @php
                                $details = [
                                    ['label' => 'شماره پرونده', 'value' => $requestRow->number, 'ltr' => true],
                                    ['label' => 'نام', 'value' => $requestRow->user_firstname],
                                    ['label' => 'نام خانوادگی', 'value' => $requestRow->user_lastname],
                                    ['label' => 'شماره همراه', 'value' => $requestRow->mobile, 'ltr' => true],
                                    ['label' => 'کد ملی', 'value' => $requestRow->user_national_id, 'ltr' => true],
                                    ['label' => 'شناسه قبض برق', 'value' => $requestRow->electricity_bill_id, 'ltr' => true],
                                    ['label' => 'نوع نیروگاه', 'value' => $requestRow->powerhouse_type],
                                    ['label' => 'استان محل نیروگاه', 'value' => $requestRow->powerhouse_place_info_province],
                                    ['label' => 'کد پستی محل نیروگاه', 'value' => $requestRow->powerhouse_place_info_postal_code, 'ltr' => true],
                                    ['label' => 'آدرس محل نیروگاه', 'value' => $requestRow->powerhouse_place_info_address],
                                    ['label' => 'ظرفیت درخواستی', 'value' => $requestRow->requested_capacity_of_powerhouse],
                                    ['label' => 'نتیجه اولین تماس', 'value' => $requestRow->first_call_result],
                                    ['label' => 'تمایل به دریافت وام', 'value' => $requestRow->loan_interest],
                                    ['label' => 'مبلغ اولیه', 'value' => $requestRow->initial_amount],
                                    ['label' => 'امکان‌سنجی', 'value' => $requestRow->feasibility_study],
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
                                    @elseif(!empty($requestRow->mobile))
                                        <span class="text-muted small">شماره جستجو: {{ $requestRow->mobile }}</span>
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
