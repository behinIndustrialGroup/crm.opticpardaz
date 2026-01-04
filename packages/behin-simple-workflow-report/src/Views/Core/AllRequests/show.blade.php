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
                <p>{{ $case->device->initial_pic }}</p>
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
@endsection
