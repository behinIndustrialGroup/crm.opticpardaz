@php
    use Behin\SimpleWorkflow\Models\Entities\Case_customer;
    use Behin\SimpleWorkflow\Models\Entities\Devices;
    use Behin\SimpleWorkflow\Models\Entities\Case_extra_docs;
    $customer = Case_customer::where('case_number', $case->number)->first();
    $device = Devices::where('case_number', $case->number)->first();
    $case->extraDocs = Case_extra_docs::where('case_number', $case->number)->get();
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
                <div class="col-sm-6">
                    تصویر اولیه:
                    <br>
                    <a href="{{ url("public/$device->initial_pic") }}" download>
                        <img src='{{ url("public/$device->initial_pic") }}' alt="" width="150">
                    </a>
                </div>
                <div class="col-sm-6">
                    تصویر پلاک دستگاه:
                    <br>
                    <a href="{{ url("public/$device->plaque_pic") }}" download>
                        <img src='{{ url("public/$device->plaque_pic") }}' alt="" width="150">
                    </a>
                </div>
            </div>

        </div>
    </div>

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
                                    @if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                                        <a href="{{ url('public/' . $doc->file) }}" target="_blank" download>
                                            <img src="{{ url('public/' . $doc->file) }}" style="max-width:100px">
                                        </a>
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
