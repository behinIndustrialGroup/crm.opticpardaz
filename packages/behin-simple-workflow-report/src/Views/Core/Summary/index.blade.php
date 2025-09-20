@extends('behin-layouts.app')

@section('title')
گزارش‌های گردش کار
@endsection


@section('content')
    <div class="container">
        <div class="row justify-content-center">

            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">گزارش‌های گردش کار</div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="d-none">شناسه</th>
                                        <th>عنوان فرآیند</th>
                                        <th class="d-none">توضیحات</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($processes as $process)
                                        @if(auth()->user()->access('خلاصه گزارش فرایند: '. $process->name))
                                            <tr>
                                                <td class="d-none">{{ $process->id }}</td>
                                                <td>{{ $process->name }}</td>
                                                <td class="d-none">{{ $process->description }}</td>
                                                <td>
                                                    <a href="{{ route('simpleWorkflowReport.summary-report.show', [ 'summary_report' => $process ]) }}" class="btn btn-primary btn-sm">مشاهده گزارش</a>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    @if(auth()->user()->access('خلاصه گزارش: تراکنش ها (هزینه ها و دستمزدها)'))
                                            <tr>
                                                <td class="d-none">تراکنش ها (هزینه ها و دستمزدها)</td>
                                                <td>تراکنش ها (هزینه ها و دستمزدها)</td>
                                                <td class="d-none"></td>
                                                <td>
                                                    <a href="{{ route('simpleWorkflowReport.transaction-report.index') }}" class="btn btn-primary btn-sm">مشاهده گزارش</a>
                                                </td>
                                            </tr>
                                    @endif
                                    @if(auth()->user()->access('خلاصه گزارش: مبالغ دریافتی پرونده ها'))
                                            <tr>
                                                <td class="d-none">مبالغ دریافتی پرونده‌ها</td>
                                                <td>مبالغ دریافتی پرونده‌ها</td>
                                                <td class="d-none"></td>
                                                <td>
                                                    <a href="{{ route('simpleWorkflowReport.repair-income-report.index') }}" class="btn btn-primary btn-sm">مشاهده گزارش</a>
                                                </td>
                                            </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
