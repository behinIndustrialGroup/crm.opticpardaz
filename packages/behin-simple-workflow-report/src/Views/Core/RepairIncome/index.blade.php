@extends('behin-layouts.app')

@section('title', 'گزارش مبالغ دریافتی پرونده‌ها')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <h5 class="mb-0">گزارش مبالغ دریافتی پرونده‌ها</h5>
                        <span class="badge bg-success fs-6">مجموع کل دریافتی‌ها: {{ number_format($overallTotal) }} ریال</span>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="repair-income-table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ردیف</th>
                                    <th>شماره پرونده</th>
                                    <th>نام مشتری</th>
                                    <th>تعداد پرداخت</th>
                                    <th>مجموع مبالغ دریافتی (ریال)</th>
                                    <th>آخرین پرداخت</th>
                                    <th>جزئیات پرداخت‌ها</th>
                                    <th>پرونده</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($caseSummaries as $index => $summary)
                                    @php
                                        $lastPayment = $summary['payments']->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $summary['case_number'] ?? '—' }}</td>
                                        <td>{{ $summary['customer_name'] ?? '—' }}</td>
                                        <td>{{ $summary['payments_count'] }}</td>
                                        <td>{{ number_format($summary['total_amount']) }}</td>
                                        <td dir="ltr">
                                            @if($lastPayment)
                                                @if(!empty($lastPayment->payment_date))
                                                    {{ $lastPayment->payment_date }}
                                                @elseif(!empty($lastPayment->created_at))
                                                    {{ toJalali($lastPayment->created_at)->format('Y-m-d H:i') }}
                                                @else
                                                    —
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            <ul class="list-unstyled mb-0 small">
                                                @foreach($summary['payments'] as $payment)
                                                    <li class="mb-1">
                                                        <span dir="ltr">
                                                            @if(!empty($payment->payment_date))
                                                                {{ $payment->payment_date }}
                                                            @elseif(!empty($payment->created_at))
                                                                {{ toJalali($payment->created_at)->format('Y-m-d H:i') }}
                                                            @else
                                                                —
                                                            @endif
                                                        </span>
                                                        - {{ number_format($payment->normalized_amount) }}
                                                        @if(!empty($payment->payment_method))
                                                            <span class="text-muted">({{ $payment->payment_method }})</span>
                                                        @endif
                                                        @if(!empty($payment->payment_description))
                                                            <div class="text-muted">{{ $payment->payment_description }}</div>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td>
                                            @if(!empty($summary['case_id']))
                                                <a href="{{ route('simpleWorkflowReport.report.edit', ['report' => $summary['case_id']]) }}" target="_blank"
                                                    class="btn btn-primary btn-sm">مشاهده پرونده</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">اطلاعاتی یافت نشد.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#repair-income-table').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'خروجی اکسل',
                    className: 'btn btn-success'
                }],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json'
                },
                ordering: false,
                pageLength: 10
            });
        });
    </script>
@endsection
