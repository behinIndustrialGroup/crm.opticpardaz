@extends('behin-layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
    <div class="card">
        <div class="card-header">
            اطلاعات مشتری
        </div>
        <div class="card-body">
            <div>
                <label for="">نام مشتری</label>
                <p>{{ $case->customer->name }}</p>
            </div>
            <div>
                <label for="">موبایل مشتری</label>
                <p>{{ $case->customer->mobile }}</p>
            </div>
            <div>
                <label for="">آدرس مشتری</label>
                <p>{{ $case->customer->address }}</p>
            </div>
        </div>
    </div>
@endsection
