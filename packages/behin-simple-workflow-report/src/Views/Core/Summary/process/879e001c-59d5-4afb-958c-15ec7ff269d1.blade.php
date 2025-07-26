@extends('behin-layouts.app')


@section('title')
    خلاصه گزارش فرایند {{ $process->name }}
@endsection

@php
    use Behin\SimpleWorkflow\Models\Entities\Case_customer;
    use Behin\SimpleWorkflow\Models\Entities\Devices;
    use Behin\SimpleWorkflow\Models\Entities\Device_repair;
@endphp

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
                                        <th>نوع تعمیر</th>
                                        <th>جزئیات نوع تعمیر</th>
                                        <th>مرحله جاری</th>
                                        <th>تاریخ پذیرش</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($process->cases as $case)
                                        @php
                                            $caseCustomer = Case_customer::where('case_number', $case->number)->first();
                                            $device = Devices::where('case_number', $case->number)->first();
                                            $deviceRepairs = Device_repair::where('case_number', $case->number)->get();

                                            $name = $caseCustomer->fullname ?? '';
                                            $mobile = $caseCustomer->mobile ?? '';
                                            $device_name = $device->name;
                                            $device_serial_no = $device->serial;
                                            $repairman = '';
                                            foreach ($deviceRepairs as $repair) {
                                                if($repair->repairman())){
                                                    $repairman .= $repair->repairman()->name . '<br>';
                                                }
                                            }
                                        @endphp

                                        @php
                                        @endphp
                                        <tr>
                                            {{-- <td>{{ $loop->iteration }}</td> --}}
                                            <td class="d-none">{{ $case->id }}</td>
                                            <td>
                                                {!! $case->history !!}
                                                {{ $case->number }}
                                                <a
                                                    href="{{ route('simpleWorkflowReport.summary-report.edit', ['summary_report' => $case->id]) }}"><i class="fa fa-external-link"></i></a>
                                            </td>
                                            <td>{{ $case->creator()?->name }}</td>

                                            <td>{{ $name }}</td>
                                            <td>{{ $mobile }}</td>
                                            <td>{{ $device_name }}</td>
                                            <td>{{ $device_serial_no }}</td>
                                            <td>{{ $repairman }}</td>
                                            <td>
                                                @foreach ($deviceRepairs as $repair)
                                                    {{ json_encode($repair->repair_type) }}<br>
                                                @endforeach
                                            </td>
                                            <td>
                                                @foreach ($deviceRepairs as $repair)
                                                    {{ json_encode($repair->repair_subtype) }}<br>
                                                @endforeach
                                            </td>
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
