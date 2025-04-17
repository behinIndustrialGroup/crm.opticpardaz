@extends('behin-layouts.app')


@section('title')
    خلاصه گزارش فرایند {{ $process->name }}
@endsection


@section('content')
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header text-center bg-warning">لیست پرونده های فرآیند {{ $process->name }}</div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="draft-list">
                                <thead>
                                    <tr>
                                        {{-- <th>ردیف</th> --}}
                                        <th class="d-none">شناسه</th>
                                        <th>شماره پرونده</th>
                                        <th>ایجاد کننده</th>
                                        <th>{{ trans('fields.customer_fullname') }}</th>
                                        <th>موبایل</th>
                                        <th>دستگاه</th>
                                        <th>سریال</th>
                                        <th>کارشناس</th>
                                        <th>مرحله جاری</th>
                                        <th>تاریخ پذیرش</th>
                                        <th>اقدام</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($process->cases as $case)
                                        @php
                                            $name = $case->variables()->where('key', 'customer_fullname')->first()
                                                ?->value;
                                            $mobile = $case->variables()->where('key', 'customer_mobile')->first()
                                                ?->value;
                                            $device_name = $case->variables()->where('key', 'device_name')->first()
                                                ?->value;
                                            $device_serial_no = $case->variables()->where('key', 'device_serial_no')->first()
                                                ?->value;
                                            $repairman = $case->variables()->where('key', 'repairman')->first()?->value;
                                            $repairman = getUserInfo($repairman)?->name ?? '';
                                            $last_status =
                                                $case->variables()->where('key', 'last_status')->first()?->value ?? '';
                                        @endphp
                                        <tr>
                                            {{-- <td>{{ $loop->iteration }}</td> --}}
                                            <td class="d-none">{{ $case->id }}</td>
                                            <td>{{ $case->number }}</td>
                                            <td>{{ $case->creator()?->name }}</td>

                                            <td>{{ $name }}</td>
                                            <td>{{ $mobile }}</td>
                                            <td>{{ $device_name }}</td>
                                            <td>{{ $device_serial_no }}</td>
                                            <td>{{ $repairman }}</td>
                                            @php
                                                $w = ' ';
                                                foreach ($case->whereIs() as $inbox) {
                                                    $w .= $inbox->task->name ?? '';
                                                    $w .= '(' . getUserInfo($inbox->actor)?->name . ')';
                                                    $w .= '<br>';
                                                }
                                            @endphp
                                            <td>{!! $w !!}</td>
                                            <td>{{ $case->getVariable('receive_date') ?? '' }}</td>
                                            <td><a
                                                    href="{{ route('simpleWorkflowReport.summary-report.edit', ['summary_report' => $case->id]) }}"><button
                                                        class="btn btn-primary btn-sm">{{ trans('fields.Show More') }}</button></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        initial_view();
        $('#draft-list').DataTable({
            "order": [
                [1, "desc"]
            ],
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Persian.json"
            },
            "dom": 'Bfrtip',
            "buttons": [{
                extend: 'excelHtml5',
                text: 'خروجی اکسل',
                titleAttr: 'خروجی اکسل',
                exportOptions: {
                    columns: ':visible'
                },
                className: 'btn btn-default',
                attr: {
                    style: 'direction: ltr'
                }
            }]
        });
    </script>
@endsection
