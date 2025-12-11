@php
    use Behin\SimpleWorkflow\Models\Entities\Case_customer;
    use Behin\SimpleWorkflow\Models\Entities\Devices;
    $customer = Case_customer::where('case_number', $case->number)->first();
    $device = Devices::where('case_number', $case->number)->first();
@endphp

<div class="card">
    <div class="card-header">
        مشخصات مشتری
    </div>
    <div class="card-body">
        نام: {{ $customer->name }}
    </div>
</div>

<div class="card">
    <div class="card-header">
        مشخصات دستگاه
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-sm-3">
                نام: {{ $device->name }}
            </div>
            <div class="col-sm-3">
                برند: {{ $device->brand }}
            </div>
            <div class="col-sm-3">
                توان: {{ $device->power }}
            </div>
            <div class="col-sm-3">
                سریال: {{ $device->serial }}
            </div>
            <div class="col-sm-3">
                تصویر اولیه: 
                <img src='{{ url('public/$device->initial_pic') }}' alt="">
            </div>
        </div>

    </div>
</div>
