@php
    use Behin\SimpleWorkflow\Models\Entities\Case_customer;
    use Behin\SimpleWorkflow\Models\Entities\Devices;
    $customer = Case_customer::where('case_number', $case->number)->first();
    $device = Devices::where('case_number', $case->number)->first();
@endphp

<div class="card">
    <div class="card-header">
        تست
    </div>
</div>



