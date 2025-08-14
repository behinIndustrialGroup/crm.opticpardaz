@extends('behin-layouts.app')


@section('content')
<style>
    .small-box {
        min-height: 150px;
        padding: 15px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: right;
        overflow: hidden;
    }

    .small-box .inner h3 {
        font-size: 18px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .small-box .inner p {
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .small-box-footer {
        font-size: 12px;
    }

    @media (max-width: 768px) {
        .small-box .inner h3,
        .small-box .inner p {
            font-size: 14px;
        }
    }
</style>
    <div class="row">
        @if (auth()->user()->access("مجموع دریافتی ها"))
                <div class="col-sm-3">
                    <!-- small box -->
                    <div class="small-box bg-light">
                        <div class="inner">
                            <h3 class="col-sm-12">{{ trans('مجموع دریافتی ها') }}</h3>

                            <p id="total-receivables" class="total-receivables">
                                <button class="btn btn-sm btn-danger" onclick="showtotalPayment()">نمایش</button>
                            </p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-pie-graph"></i>
                        </div>
                        <a href="#"
                        class="small-box-footer">{{ trans('مشاهده') }} <i class="fa fa-arrow-circle-left"></i></a>
                        <script>
                            function showtotalPayment(){
                                send_ajax_get_request(
                                "{{ route('simpleWorkflowReport.totalPayment') }}",
                                function(response) {

                                    $('#total-receivables').text(parseInt(response.replace(/,/g, '')).toLocaleString() + ' ریال');
                                    // runCamaSeprator('total-receivables');

                                }
                            )
                            }
                            
                        </script>
                    </div>

                </div>
            @endif
        @if (auth()->user()->access('منو >>کارتابل>>فرایند جدید'))
            <div class="col-sm-3 ">
                <!-- small box -->
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ trans('پذیرش دستگاه') }}</h3>

                        <p>{{ trans('ثبت پذیرش دستگاه جدید') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="{{ route('simpleWorkflow.process.start', [
                        'taskId' => '7434396b-54ec-4624-840e-e7b24db73eaf',
                        'inDraft' => 0,
                        'force' => 1,
                        'redirect' => true,
                        ]) }}" class="small-box-footer">{{ trans('مشاهده') }} <i
                            class="fa fa-arrow-circle-left"></i></a>
                </div>
            </div>
        @endauth
        @if (auth()->user()->access('پذیرش دستگاه برگشتی'))
            <div class="col-sm-3 ">
                <!-- small box -->
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ trans('پذیرش دستگاه برگشتی') }}</h3>

                        <p>{{ trans('پذیرش دستگاه برگشتی') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="{{ route('simpleWorkflow.process.start', [
                        'taskId' => '3629b18d-703b-49fa-b4f2-f55436a887cb',
                        'inDraft' => true,
                        'force' => 1,
                        'redirect' => true,
                        ]) }}" class="small-box-footer">{{ trans('مشاهده') }} <i
                            class="fa fa-arrow-circle-left"></i></a>
                </div>
            </div>
        @endauth
        @if (auth()->user()->access('منو >>کارتابل>>کارتابل'))
            <div class="col-sm-3 ">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ trans('کارتابل من') }}</h3>

                        <p>{{ trans('لیست پرونده هایی که باید انجام دهید') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-bag"></i>
                    </div>
                    <a href="{{ route('simpleWorkflow.inbox.index') }}" class="small-box-footer">{{ trans('مشاهده') }} <i
                            class="fa fa-arrow-circle-left"></i></a>
                </div>
            </div>
        @endauth
        @if (auth()->user()->access('لیست کارها'))
            <div class="col-sm-3 ">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ trans('لیست کارها') }}</h3>

                        <p>{{ trans('لیست کارهایی که ابلاغ شده است') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-stats-bars"></i>
                    </div>
                    <a href="{{ route('todoList.index') }}" class="small-box-footer">{{ trans('مشاهده') }} <i
                            class="fa fa-arrow-circle-left"></i></a>
                </div>
            </div>
        @endif
        @if (auth()->user()->access('منو >>گزارشات کارتابل>>لیست'))
            <div class="col-sm-3 ">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ trans('گزارش پرونده ها') }}</h3>

                        <p>{{ trans('گزارش پرونده ها بر اساس وضعیت') }}</p>
                    </div>
                    <div class="icon">
                        <i class="ion ion-pie-graph"></i>
                    </div>
                    <a href="{{ route('simpleWorkflowReport.summary-report.index') }}"
                        class="small-box-footer">{{ trans('مشاهده') }} <i class="fa fa-arrow-circle-left"></i></a>
                </div>
            </div>
        @endif

            
    </div>

    @endsection

    @section('script')
 
    @endsection
