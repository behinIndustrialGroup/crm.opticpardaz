@extends('behin-layouts.app')

@section('title', 'گزارش تراکنش‌ها')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">گزارش تراکنش‌ها</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped table-hover" id="transaction-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>ردیف</th>
                            <th>تاریخ</th>
                            <th>نوع تراکنش</th>
                            <th>مبلغ</th>
                            <th>شرح</th>
                            <th>دسته‌بندی</th>
                            <th>پرونده</th>
                            <th>شماره پرونده</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $index => $transaction)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td dir="ltr">{{ toJalali($transaction->created_at)->format('Y-m-d H:i') }}</td>
                                <td>{{ $transaction->transaction_type }}</td>
                                <td>{{ number_format($transaction->amount) }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->category }}</td>
                                <td>{{ $transaction->counterparty }}</td>
                                <td>{{ $transaction->case()->number ?? '' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="21" class="text-center">تراکنشی یافت نشد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection


@section('script')
    <script>
        $(document).ready(function() {
            $('#transaction-table').DataTable({
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
