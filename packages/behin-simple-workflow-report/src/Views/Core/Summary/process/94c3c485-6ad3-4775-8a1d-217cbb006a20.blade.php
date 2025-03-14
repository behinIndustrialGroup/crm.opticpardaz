@extends('behin-layouts.app')

@php
    $inventoryReport = DB::table('wf_entity_inventory_items')
        ->select('warehouse_name', 'product_name', 'quantity', DB::raw('quantity * purchase_price AS total_value'))
        ->orderBy('warehouse_name')
        ->get();

    $inventorySummary = DB::table('wf_entity_inventory_items')
        ->select(
            'product_name',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(quantity * purchase_price) as total_value'),
        )
        ->groupBy('product_name')
        ->orderBy('product_name')
        ->get();
@endphp

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
                    <div class="card-header text-center bg-warning"></div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="draft-list">
                                <thead class="table-dark">
                                    <tr>
                                        <th>نام انبار</th>
                                        <th>نام کالا</th>
                                        <th>تعداد</th>
                                        <th>ارزش کل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventoryReport as $item)
                                        <tr>
                                            <td>{{ $item->warehouse_name }}</td>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ number_format($item->quantity) }}</td>
                                            <td>{{ number_format($item->total_value) }} ریال</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">هیچ داده‌ای یافت نشد!</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <hr>
                            <hr>
                            <h2 class="mb-4">گزارش تجمیعی کالاها</h2>
                            <table class="table table-bordered table-striped" id="inventory-summary">
                                <thead class="table-dark">
                                    <tr>
                                        <th>نام کالا</th>
                                        <th>مجموع تعداد</th>
                                        <th>مجموع ارزش کل</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventorySummary as $item)
                                        <tr>
                                            <td>{{ $item->product_name }}</td>
                                            <td>{{ number_format($item->total_quantity) }}</td>
                                            <td>{{ number_format($item->total_value) }} ریال</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center">هیچ داده‌ای یافت نشد!</td>
                                        </tr>
                                    @endforelse
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

        $('#inventory-summary').DataTable({
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
        })
    </script>
@endsection
