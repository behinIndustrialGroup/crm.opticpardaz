@extends('behin-layouts.app')

@section('title', 'جزئیات درخواست')

@section('content')
    <input type="hidden" id="caseId" value="{{ $case->id }}">
    <div class="card row">
        <div class="card-header">
            اطلاعات مشتری
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام مشتری</label>
                <p>{{ $case->customer->fullname }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">موبایل مشتری</label>
                <p>{{ $case->customer->mobile }}</p>
            </div>
            <div class="col-sm-12">
                <label for="">آدرس مشتری</label>
                <p>{{ $case->customer->address }}</p>
            </div>
        </div>
    </div>
    <div class="card row">
        <div class="card-header">
            اطلاعات دستگاه
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام دستگاه</label>
                <p>{{ $case->device->name }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">سری دستگاه</label>
                <p>{{ $case->device->brand }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">توان دستگاه</label>
                <p>{{ $case->device->power }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">سریال دستگاه</label>
                <p>{{ $case->device->serial }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">تصویر اولیه دستگاه</label>
                <p>
                    @if ($case->device->initial_pic)
                        <img src="{{ url('public/' . $case->device->initial_pic) }}" alt="" width="100" download>
                    @endif
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">تصویر پلاک دستگاه</label>
                <p>
                    @if ($case->device->plaque_pic)
                        <img src="{{ url('public/' . $case->device->plaque_pic) }}" alt="" width="100" download>
                    @endif
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">مشخصات دستگاه</label>
                <p>{{ $case->device->specifications }}</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات تعمیرات
        </div>
        <div class="card-body row">
            <div class="col-sm-3">
                <label for="">نام تعمیرکار</label>
                <p>{{ getUserInfo($case->deviceRepair?->repairman)?->name ?? $case->deviceRepair?->repairman }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">نوع تعمیر</label>
                <p>
                    @if ($case->deviceRepair?->repair_type)
                        @php
                            $repairType = normalizeList($case->deviceRepair?->repair_type);
                        @endphp
                        @foreach ($repairType as $type)
                            <span class="badge bg-primary">{{ $type }}</span>
                        @endforeach
                    @endif
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">جزئیات نوع تعمیر</label>
                <p>
                    @php
                        $repairSubtype = normalizeList($case->deviceRepair?->repair_subtype);
                    @endphp
                    @foreach ($repairSubtype as $subtype)
                        <span class="badge bg-primary">{{ $subtype }}</span>
                    @endforeach
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">تاریخ شروع تعمیر</label>
                <p>{{ $case->deviceRepair?->repair_start_date }}</p>
            </div>
            <div class="col-sm-3">
                <label for="">نام دستیار تعمیرکار</label>
                <p>
                    @if ($assistants)
                        @foreach ($assistants as $assitant)
                            {{ $assitant?->name }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        {{-- @if (gettype($case->deviceRepair?->repairman_assitant) == 'string')
                            string
                        @elseif(gettype($case->deviceRepair?->repairman_assitant) == 'array')
                            @foreach ($case->deviceRepair?->repairman_assitant as $assitant)
                                array
                            @endforeach
                        @else
                            @foreach ($case->deviceRepair?->repairman_assitant as $assitant)
                                {{ getUserInfo($assitant)?->name }}
                                @if (!$loop->last)
                                    ,
                                @endif
                            @endforeach
                        @endif --}}
                    @endif
                </p>
            </div>
            <div class="col-sm-3">
                <label for="">تاریخ پایان تعمیر</label>
                <p>{{ $case->deviceRepair?->repair_end_date }}</p>
            </div>
            <div class="col-sm-12">
                <label for="">گزارش تعمیر</label>
                <p>{{ $case->deviceRepair?->repair_report }}</p>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            تصاویر تعمیرات
        </div>
        <div class="card-body row">
            @foreach ($case->deviceRepairPics as $pic)
                <div class="col-sm-3">
                    <img src="{{ asset($pic->file) }}" alt="">
                </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات تعیین هزینه
        </div>
        <div class="card-body row">
            @foreach ($case->repairCosts as $cost)
                <div class="col-sm-9">
                    <label for="">توضیحات</label>
                    <p>{{ $cost->description }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">هزینه</label>
                    <p>{{ $cost->cost }}</p>
                </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            اطلاعات پرداخت
        </div>
        <div class="card-body row">
            @foreach ($case->repairIncomes as $income)
                <div class="col-sm-3">
                    <label for="">نوع پرداختی</label>
                    <p>{{ $income->payment_method }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">رسید پرداختی</label>
                    <p>
                        @if ($income->payment_receipt)
                            <img src="{{ url('public/' . $income->payment_receipt) }}" alt="" width="150">
                        @endif
                    </p>
                </div>
                <div class="col-sm-3">
                    <label for="">تاریخ پرداختی</label>
                    <p>{{ $income->payment_date }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">مبلغ پرداختی</label>
                    <p>{{ $income->payment_amount }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">توضیحات پرداختی</label>
                    <p>{{ $income->payment_description }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">شماره تراکنش</label>
                    <p>{{ $income->transaction_number }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">شماره چک</label>
                    <p>{{ $income->cheque_number }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">تاریخ سررسید چک</label>
                    <p>{{ $income->cheque_due_date }}</p>
                </div>
                <div class="col-sm-3">
                    <label for="">تایید پرداختی</label>
                    <p>{{ $income->income_is_approved }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @if (access('تفکیک هزینه ها در مشاهده جزئیات بیشتر هر پرونده'))
        <div class="card">
            <div class="card-header">
                تفکیک هزینه ها و دستمزد ها و پاداش ها
            </div>
            <div class="card-body row table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>نوع تراکنش</th>
                            <th>مبلغ</th>
                            <th>توضیحات</th>
                            <th>طرف حساب</th>
                            <th>دسته بندی</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($case->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->transaction_type }}</td>
                                <td>{{ $transaction->amount }}</td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ $transaction->counterparty }}</td>
                                <td>{{ $transaction->catagory }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    @endif
    @php
        $fieldName = 'فایل های مرتبط با پرونده';
        $fieldDetails = getFieldDetailsByName($fieldName);
        $fieldValue = isset($case) ? $case->getVariable($fieldName) : null;
        $fieldValueAlt =
            (isset($case) and in_array($fieldDetails->type, ['datetime', 'date']))
                ? $case->getVariable($fieldName . '_alt')
                : null;
    @endphp
    <div class="">
        <p class="bg-warning text-center card">دقت داشته باشید این فایل های زیر به مشتری نمایش داده خواهد شد</p>
        @include('SimpleWorkflowView::Core.Form.field-generator', [
            'fieldName' => $fieldName,
            'fieldId' => $fieldName,
            'fieldClass' => 'col-sm-12',
            'readOnly' => true,
            'required' => false,
            'fieldValue' => $fieldValue,
            'fieldValueAlt' => $fieldValueAlt ?? '',
        ])
    </div>
@endsection
