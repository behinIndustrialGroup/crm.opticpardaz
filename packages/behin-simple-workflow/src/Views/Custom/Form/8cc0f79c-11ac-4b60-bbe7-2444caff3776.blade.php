@php
    use Behin\SimpleWorkflow\Models\Entities;
    use Behin\SimpleWorkflow\Models\Entities\Case_customer;
    use Behin\SimpleWorkflow\Models\Entities\Devices;
    use Behin\SimpleWorkflow\Models\Entities\Case_extra_docs;
    use Behin\SimpleWorkflow\Models\Entities\Pre_invoice_items;
    $customer = Case_customer::where('case_number', $case->number)->first();
    $device = Devices::where('case_number', $case->number)->first();
    $case->extraDocs = Case_extra_docs::where('case_number', $case->number)->get();
    $case->preInvoiceItems = Pre_invoice_items::where('case_number', $case->number)->get();
    $case->repairCosts = Entities\Repair_cost::where('case_number', $case->number)->get();

    if(auth()->id() and request()->method == 'post'){
        $path = request()->file('payment_receipt');
        dd($path);
    }
@endphp

<div class="container">
    <div class="card">
        <div class="card-header bg-success">
            مشخصات مشتری
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                نام: {{ $customer->fullname }}
            </div>
            <div class="col-sm-3">
                شماره پرونده: {{ $case->number }}
            </div>
        </div>

    </div>

    <div class="card">
        <div class="card-header bg-info">
            مشخصات دستگاه
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-3">
                    نام: {{ $device->name }}
                </div>
                <div class="col-sm-3">
                    برند: {{ $device->brand }}
                </div>
                <div class="col-sm-3">
                    توان: {{ $device->power }}
                </div>
                <div class="col-sm-3">
                    سریال: {{ $device->serial }}
                </div>
                <div class="col-sm-3">
                    تصویر اولیه:
                    <br>
                    @if (str_contains($device->initial_pic, 'http'))
                        <a href="{{ $device->initial_pic }}" target="_blank" download>دانلود</a>
                    @else
                        <a href="{{ url('public/' . $device->initial_pic) }}" target="_blank" download>دانلود</a>
                    @endif
                </div>
                <div class="col-sm-3">
                    تصویر پلاک دستگاه:
                    <br>
                    @if (str_contains($device->plaque_pic, 'http'))
                        <a href="{{ $device->plaque_pic }}" target="_blank" download>دانلود</a>
                    @else
                        <a href="{{ url('public/' . $device->plaque_pic) }}" target="_blank" download>دانلود</a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if (auth()->id())
        <div class="card">

            <div class="card-header" id="preInvoiceHeader" style="cursor:pointer;">
                <span>پیش فاکتور</span>
                <span id="toggleIcon">
                    <i class="fa fa-plus"></i>
                </span>
            </div>

            <div class="card-body" id="preInvoiceBody">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>شرح کالا/خدمات</th>
                            <th>قیمت واحد</th>
                            <th>تعداد</th>
                            <th>قیمت کل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($case->preInvoiceItems as $preInvoiceItem)
                            <tr>
                                <td>{{ $preInvoiceItem->name ?? '' }}</td>
                                <td>{{ number_format($preInvoiceItem->unit_price) ?? '' }}</td>
                                <td>{{ $preInvoiceItem->number ?? '' }}</td>
                                <td>{{ number_format($preInvoiceItem->price) ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                اطلاعات پرداخت
            </div>
            <div class="card-body row">
                <div class="col-sm-6">
                    <label for="">مبلغ قابل پرداخت</label>
                    <div class="">
                        @foreach ($case->repairCosts as $cost)
                            {{ number_format($cost->cost) ?? '' }}
                            @if (!$loop->last)
                                <br>
                            @endif
                        @endforeach
                    </div>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="col-sm-6">
                        <label for="">بارگزاری رسید پرداخت</label>
                        <input type="file" name="payment_receipt" id="" class="form-control">
                    </div>
                    <input type="submit" name="submit" id="" value="ثبت">
                </form>
            </div>
        </div>


    @endif

    <div class="card">
        <div class="card-header bg-warning">
            آخرین وضعیت
        </div>
        <div class="card-body">
            {{ $case->getVariable('last_status') }}
        </div>
    </div>

    @if (count($case->extraDocs))
        <div class="card">
            <div class="card-header bg-primary">
                فایل های مرتبط با پرونده
            </div>
            <div class="card-body row">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>نام</th>
                            <th>فایل</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($case->extraDocs as $doc)
                            @php
                                $ext = strtolower(pathinfo($doc->file, PATHINFO_EXTENSION));
                            @endphp
                            <tr>
                                <td>{{ $doc->name }}</td>
                                <td>
                                    @if (str_contains($doc->file, 'http'))
                                        <a href="{{ $doc->file }}" target="_blank" download>دانلود</a>
                                    @else
                                        <a href="{{ url('public/' . $doc->file) }}" target="_blank" download>دانلود</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<script>
    $('#preInvoiceBody').css('display', 'none');

    $('#preInvoiceHeader').on('click', function() {
        if ($('#preInvoiceBody').css('display') == 'none') {
            $('#preInvoiceBody').css('display', 'block');
            $('#toggleIcon i').removeClass('fa-plus').addClass('fa-minus');
        } else {
            $('#preInvoiceBody').css('display', 'none');
            $('#toggleIcon i').removeClass('fa-minus').addClass('fa-plus');
        }
    });
</script>
