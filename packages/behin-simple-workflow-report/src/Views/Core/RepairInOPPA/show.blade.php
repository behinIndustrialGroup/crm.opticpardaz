@extends('behin-layouts.app')

@section('title', 'Case Details')

@section('content')
    <div class="container py-4">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Case --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-primary fw-bold">📁 Case Information</h5>
            </div>
            <div class="card-body">
                <p class="card-text fs-5">{{ $case->number }}</p>
            </div>
        </div>

        {{-- Customer --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-success fw-bold">👤 Customer Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-4"><strong>{{ trans('fields.customer_fullname') }}:</strong>
                        {{ $customer['name'] }}</div>
                    <div class="col-md-6 col-lg-4"><strong>{{ trans('fields.customer_national_id') }}:</strong>
                        {{ $customer['national_id'] }}</div>
                    <div class="col-md-6 col-lg-4"><strong>{{ trans('fields.customer_mobile') }}:</strong>
                        {{ $customer['mobile'] }}</div>
                    <div class="col-md-12"><strong>{{ trans('fields.customer_address') }}:</strong>
                        {{ $customer['address'] }}</div>
                </div>
            </div>
        </div>

        {{-- Device --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-info fw-bold">🛠️ Device Details</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <form action="{{ route('simpleWorkflowReport.oppa-report.update', $case->id) }}" method="POST"
                        class="row g-3" onsubmit="confirm('آیا می خواهید اطلاعات دستگاه را ویرایش کنید؟')">
                        @csrf
                        @method('PUT')
                        <input type="text" name="form_type" id="form_type" value="device" hidden>
                        <input type="text" name="device_id" id="device_id" value="{{ $device->id }}" hidden>
                        <div class="col-md-6 col-lg-4">
                            @include('SimpleWorkflowView::Core.Form.field-generator', [
                                'fieldName' => 'device_name',
                                'fieldId' => 'device_name',
                                'fieldClass' => 'form-control',
                                'readOnly' => false,
                                'required' => false,
                                'fieldValue' => $device->name,
                            ])
                        </div>
                        <div class="col-md-6 col-lg-4">
                            @include('SimpleWorkflowView::Core.Form.field-generator', [
                                'fieldName' => 'device_power',
                                'fieldId' => 'device_power',
                                'fieldClass' => 'form-control',
                                'readOnly' => false,
                                'required' => false,
                                'fieldValue' => $device->power,
                            ])
                        </div>
                        <div class="col-md-6 col-lg-4">
                            @include('SimpleWorkflowView::Core.Form.field-generator', [
                                'fieldName' => 'device_brand',
                                'fieldId' => 'device_brand',
                                'fieldClass' => 'form-control',
                                'readOnly' => false,
                                'required' => false,
                                'fieldValue' => $device->brand,
                            ])
                        </div>
                        <div class="col-md-6 col-lg-4">
                            @include('SimpleWorkflowView::Core.Form.field-generator', [
                                'fieldName' => 'device_serial_no',
                                'fieldId' => 'device_serial_no',
                                'fieldClass' => 'form-control',
                                'readOnly' => false,
                                'required' => false,
                                'fieldValue' => $device->serial,
                            ])
                        </div>
                        <div class="col-md-6 col-lg-4"><strong>{{ trans('fields.device_initial_pic') }}:</strong>
                            @if ($device->initial_pic)
                                <a href="{{ url('public/' . $device->initial_pic) }}"
                                    download="{{ trans('fields.device_initial_pic') . $case->number }}.jpg">
                                    <img src="{{ url('public/' . $device->initial_pic) }}" alt="Initial Picture"
                                        width="100" class="img-fluid">
                                </a>
                            @endif
                        </div>
                        <div class="col-md-6 col-lg-4"><strong>{{ trans('fields.device_plaque_pic') }}:</strong>
                            @if ($device->plaque_pic)
                                <a href="{{ url('public/' . $device->plaque_pic) }}"
                                    download="{{ trans('fields.device_plaque_pic') . $case->number }}.jpg">
                                    <img src="{{ url('public/' . $device->plaque_pic) }}" alt="Plaque Picture"
                                        width="100" class="img-fluid">
                                </a>
                            @endif
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">{{ trans('fields.Update') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Repairs --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-warning fw-bold">🔧 Repairs</h5>
            </div>
            <div class="card-body">
                @if (count($repairs))
                    @foreach ($repairs as $repair)
                        <form action="{{ route('simpleWorkflowReport.oppa-report.update', $case->id) }}" method="POST"
                            class="row g-3" onsubmit="confirm('آیا می خواهید اطلاعات دستگاه را ویرایش کنید؟')">
                            @csrf
                            @method('PUT')
                            <input type="text" name="form_type" id="form_type" value="repair" hidden>
                            <input type="text" name="repair_id" id="repair_id" value="{{ $repair->id }}" hidden>
                            <div class="col-md-6 col-lg-3">
                                <label>{{ trans('fields.repair_start_date') }}:</label>
                                {{ toJalali((int)$repair->repair_start_timestamp)->format('Y-m-d') }}
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label>{{ trans('fields.device_pic') }}:</label>
                                @if ($repair->device_pic)
                                    <a href="{{ url('public/' . $repair->device_pic) }}"
                                        download="{{ trans('fields.device_pic') . $case->number }}.jpg">
                                        <img src="{{ url('public/' . $repair->device_pic) }}" alt="Device Picture"
                                            width="100" class="img-fluid">
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-6 col-lg-6"></div>
                            <div class="col-md-6 col-lg-3">
                                @include('SimpleWorkflowView::Core.Form.field-generator', [
                                    'fieldName' => 'repairman',
                                    'fieldId' => 'repairman',
                                    'fieldClass' => 'form-control',
                                    'readOnly' => true,
                                    'required' => false,
                                    'fieldValue' => $repair->repairman,
                                ])
                            </div>
                            <div class="col-md-6 col-lg-3">
                                @include('SimpleWorkflowView::Core.Form.field-generator', [
                                    'fieldName' => 'repairman_assitant',
                                    'fieldId' => 'repairman_assitant',
                                    'fieldClass' => 'form-control',
                                    'readOnly' => false,
                                    'required' => false,
                                    'fieldValue' => $repair->repairman_assitant,
                                ])
                            </div>

                            <div class="col-md-6 col-lg-3">
                                @include('SimpleWorkflowView::Core.Form.field-generator', [
                                    'fieldName' => 'repair_type',
                                    'fieldId' => 'repair_type',
                                    'fieldClass' => 'form-control',
                                    'readOnly' => false,
                                    'required' => false,
                                    'fieldValue' => $repair->repair_type,
                                ])
                            </div>

                            <div class="col-md-6 col-lg-3">
                                @include('SimpleWorkflowView::Core.Form.field-generator', [
                                    'fieldName' => 'repair_subtype',
                                    'fieldId' => 'repair_subtype',
                                    'fieldClass' => 'form-control',
                                    'readOnly' => false,
                                    'required' => false,
                                    'fieldValue' => $repair->repair_subtype,
                                ])
                            </div>

                            <div class="col-md-12">
                                @include('SimpleWorkflowView::Core.Form.field-generator', [
                                    'fieldName' => 'repair_report',
                                    'fieldId' => 'repair_report',
                                    'fieldClass' => 'form-control',
                                    'readOnly' => false,
                                    'required' => false,
                                    'fieldValue' => $repair->repair_report,
                                ])
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">{{ trans('fields.Update') }}</button>
                            </div>
                        </form>
                    @endforeach
                @else
                    <p class="text-muted">No repairs found.</p>
                @endif
            </div>
        </div>

        {{-- Repair Cost --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-danger fw-bold">💰 Repair Cost</h5>
            </div>
            <div class="card-body">
                <p class="fs-5">{{ $repairCost->cost ?? '—' }}</p>
            </div>
        </div>

        {{-- Repair Incomes --}}
        <div class="card shadow-sm mb-4 border-0 rounded-4">
            <div class="card-header">
                <h5 class="card-title text-secondary fw-bold">📈 Repair Incomes</h5>
            </div>
            <div class="card-body">
                @if (count($repairIncomes))
                    <ul class="list-group list-group-flush">
                        @foreach ($repairIncomes as $repairIncome)
                            <li class="list-group-item">{{ $repairIncome->income }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No income records found.</p>
                @endif
            </div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        initial_view();
    </script>


@endsection