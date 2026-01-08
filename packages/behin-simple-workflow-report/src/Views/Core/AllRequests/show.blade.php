@extends('behin-layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
    <div class="card row">
        <div class="card-header">
            اطلاعات مشتری
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام مشتری</label>
                <p>{{ $case->customer->name }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">موبایل مشتری</label>
                <p>{{ $case->customer->mobile }}</p>
            </div>
            <div class="col-sm-12">
                <label for="">آدرس مشتری</label>
                <p>{{ $case->customer->address }}</p>
            </div>
        </div>
    </div>
    <div class="card row">
        <div class="card-header">
            اطلاعات دستگاه
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام دستگاه</label>
                <p>{{ $case->device->name }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">سری دستگاه</label>
                <p>{{ $case->device->brand }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">توان دستگاه</label>
                <p>{{ $case->device->power }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">سریال دستگاه</label>
                <p>{{ $case->device->serial }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">تصویر اولیه دستگاه</label>
                <p>
                    @if ($case->device->initial_pic)
                        <img src="{{ url($case->device->initial_pic) }}" alt="">
                    @endif
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">تصویر پلاک دستگاه</label>
                <p>{{ $case->device->plaque_pic }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">مشخصات دستگاه</label>
                <p>{{ $case->device->specifications }}</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات تعمیرات
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام تعمیرکار</label>
                <p>{{ getUserInfo($case->deviceRepair->repairman)?->name ?? $case->deviceRepair->repairman  }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">نوع تعمیر</label>
                <p>{{ $case->deviceRepair->repair_type }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">جزئیات نوع تعمیر</label>
                <p>{{ $case->deviceRepair->repair_subtype }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">تاریخ شروع تعمیر</label>
                <p>{{ $case->deviceRepair->repair_start_date }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">نام دستیار تعمیرکار</label>
                <p>{{ $case->deviceRepair->repairman_assitant }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">تاریخ پایان تعمیر</label>
                <p>{{ $case->deviceRepair->repair_end_date }}</p>
            </div>
            <div class="col-sm-12">
                <label for="">گزارش تعمیر</label>
                <p>{{ $case->deviceRepair->repair_report }}</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            تصاویر تعمیرات
        </div>
        <div class="card-body row">
            @foreach ($case->deviceRepairPics as $pic)
                <div class="col-sm-3">
                    <img src="{{ asset($pic->file) }}" alt="">
                </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات تعیین هزینه
        </div>
        <div class="card-body row">
            @foreach ($case->repairCosts as $cost)
                <div class="col-sm-9">
                    <label for="">توضیحات</label>
                    <p>{{ $cost->description }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">هزینه</label>
                    <p>{{ $cost->cost }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات پرداخت
        </div>
        <div class="card-body row">
            @foreach ($case->repairIncomes as $income)
                <div class="col-sm-3">
                    <label for="">نوع پرداختی</label>
                    <p>{{ $income->payment_method }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">رسید پرداختی</label>
                    <p>{{ $income->payment_receipt }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">تاریخ پرداختی</label>
                    <p>{{ $income->payment_date }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">مبلغ پرداختی</label>
                    <p>{{ $income->payment_amount }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">توضیحات پرداختی</label>
                    <p>{{ $income->payment_description }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">شماره تراکنش</label>
                    <p>{{ $income->transaction_number }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">شماره چک</label>
                    <p>{{ $income->cheque_number }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">تاریخ سررسید چک</label>
                    <p>{{ $income->cheque_due_date }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">تایید پرداختی</label>
                    <p>{{ $income->income_is_approved }}</p>
                </div>
            @endforeach
        </div>
    </div>
@endsection
