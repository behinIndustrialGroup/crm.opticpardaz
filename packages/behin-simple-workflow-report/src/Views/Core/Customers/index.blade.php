@extends('behin-layouts.app')

@section('title', 'مدیریت مشتریان')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">مدیریت مشتریان</h5>
                <button class="btn btn-primary" data-toggle="modal" data-target="#createCustomerModal">
                    <i class="fa fa-plus"></i>
                    افزودن مشتری جدید
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="customers-table">
                        <thead class="thead-light">
                            <tr>
                                <th>ردیف</th>
                                <th>نام و نام خانوادگی</th>
                                <th>کد ملی</th>
                                <th>شماره موبایل</th>
                                <th>آدرس</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($customers as $customer)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $customer->fullname }}</td>
                                    <td>{{ $customer->national_id ?? '—' }}</td>
                                    <td>{{ $customer->mobile ?? '—' }}</td>
                                    <td>{!! nl2br(e($customer->address ?? '—')) !!}</td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-outline-secondary"
                                            data-toggle="modal"
                                            data-target="#editCustomerModal"
                                            data-action="{{ route('simpleWorkflowReport.customers.update', $customer) }}"
                                            data-fullname="{{ e($customer->fullname) }}"
                                            data-national-id="{{ e($customer->national_id) }}"
                                            data-mobile="{{ e($customer->mobile) }}"
                                            data-address="{{ e($customer->address) }}">
                                            <i class="fa fa-pencil"></i>
                                            ویرایش
                                        </button>
                                        <form class="d-inline"
                                            action="{{ route('simpleWorkflowReport.customers.destroy', $customer) }}"
                                            method="POST"
                                            onsubmit="return confirm('آیا از حذف این مشتری مطمئن هستید؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa fa-trash"></i>
                                                حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">مشتری یافت نشد.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createCustomerModal" tabindex="-1" role="dialog"
        aria-labelledby="createCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createCustomerModalLabel">افزودن مشتری جدید</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="{{ route('simpleWorkflowReport.customers.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="create-fullname">نام و نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="create-fullname" name="fullname"
                                value="{{ old('fullname') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="create-national-id">کد ملی</label>
                            <input type="text" class="form-control" id="create-national-id" name="national_id"
                                value="{{ old('national_id') }}">
                        </div>
                        <div class="form-group">
                            <label for="create-mobile">شماره موبایل</label>
                            <input type="text" class="form-control" id="create-mobile" name="mobile"
                                value="{{ old('mobile') }}">
                        </div>
                        <div class="form-group">
                            <label for="create-address">آدرس</label>
                            <textarea class="form-control" id="create-address" name="address" rows="3">{{ old('address') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ثبت مشتری</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" role="dialog"
        aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCustomerModalLabel">ویرایش مشتری</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="بستن">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST" action="#" id="edit-customer-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit-fullname">نام و نام خانوادگی <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-fullname" name="fullname" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-national-id">کد ملی</label>
                            <input type="text" class="form-control" id="edit-national-id" name="national_id">
                        </div>
                        <div class="form-group">
                            <label for="edit-mobile">شماره موبایل</label>
                            <input type="text" class="form-control" id="edit-mobile" name="mobile">
                        </div>
                        <div class="form-group">
                            <label for="edit-address">آدرس</label>
                            <textarea class="form-control" id="edit-address" name="address" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">انصراف</button>
                        <button type="submit" class="btn btn-primary">ذخیره تغییرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('#customers-table').DataTable({
                dom: 'Bfrtip',
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/fa.json'
                },
                buttons: [{
                    text: 'خروجی اکسل',
                    className: 'btn btn-success',
                    action: function() {
                        window.location.href = '{{ route('simpleWorkflowReport.customers.export') }}';
                    }
                }],
                order: [],
                pageLength: 10
            });

            $('#editCustomerModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const action = button.data('action');
                const fullname = button.data('fullname') || '';
                const nationalId = button.data('nationalId') || '';
                const mobile = button.data('mobile') || '';
                const address = button.data('address') || '';

                const modal = $(this);
                const form = modal.find('form');

                form.attr('action', action);
                form.find('input[name="fullname"]').val(fullname);
                form.find('input[name="national_id"]').val(nationalId);
                form.find('input[name="mobile"]').val(mobile);
                form.find('textarea[name="address"]').val(address);
            });
        });
    </script>
@endsection
