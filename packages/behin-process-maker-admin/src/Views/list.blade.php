@extends(config('pm_config.layout_name'))

@section('content')
    <div class="col-sm-12" style="height: 10px;"></div>
    <div class="card row table-responsive" style="padding: 5px">
        <table class="table table-striped " id="list">
            <thead>
                <tr>
                    {{-- <th>{{__('Id')}}</th> --}}
                    <th>{{__('Case')}}</th>
                    <th>{{__('Customer Fullname')}}</th>
                    <th>{{__('Receive Date')}}</th>
                    <th>{{__('Device Name')}}</th>
                    <th>{{__('Repairman')}}</th>
                    <th>{{__('Repair Report')}}</th>
                    <th>{{__('Repair Is Approved')}}</th>
                    <th>{{__('Repair Declined Description')}}</th>
                    <th>{{__('Repair Is Approved 2')}}</th>
                    <th>{{__('Repair Declined Description 2')}}</th>
                    <th>{{__('Repair Is Approved 3')}}</th>
                    <th>{{__('Repair Declined Description 3')}}</th>
                    <th>{{__('Repair Cost')}}</th>
                    {{-- <th>{{__('Current User')}}</th> --}}
                    <th>{{__('Status')}}</th>
                    <th>{{__('Delete')}}</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('script')
    <script>
        var table = create_datatable(
            'list',
            '{{ route("pmAdmin.api.all") }}',
            [
                // {data : 'APP_UID', render: function(APP_UID){return APP_UID.substr(APP_UID.length - 8)}},
                {data : 'case_id',
                    visible: false
                },
                {data : 'customer_fullname'},
                {data : 'receive_date'},
                {data : 'device_name'},
                {data : 'repairman'},
                {data : 'repair_report'},
                {data : 'repair_is_approved'},
                {data : 'repair_declined_description'},
                {data : 'repair_is_approved_2'},
                {data : 'repair_declined_description_2'},
                {data : 'repair_is_approved_3'},
                {data : 'repair_declined_description_3'},
                {data : 'repair_cost'},
                // {data : 'caseInfo.task.currentUser'},
                {data : 'status'},
                {data : 'case_id', render: function(data, type, row){
                    return `<button class='btn btn-danger' onclick="delete_case_info_from_db('${row.process_id}', '${data}')">delete</button>`;
                }}
            ],
            function(row){
                $(row).css('cursor', 'pointer')
            },
            [ 2, 'desc' ]
        );
        table.on('dblclick', 'tr', function(){
            var data = table.row( this ).data();
            console.log(data);
            var fd = new FormData();
            fd.append('caseId', data.case_id);
            fd.append('processId', data.process_id);
            url = "{{ route('pmAdmin.form.caseDetails') }}";
            console.log(url);
            send_ajax_formdata_request(
                url,
                fd,
                function(response){
                    console.log(response);
                    open_admin_modal_with_data(response, '' , function(){initial_view()})
                }
            )
        })

        function delete_case_info_from_db(processId,caseId){
            url = "{{ route('pmAdmin.api.deleteCase') }}";
            var fd = new FormData();
            fd.append('processId', processId);
            fd.append('caseId', caseId);
            send_ajax_formdata_request_with_confirm(
                url,
                fd,
                function(response){
                    console.log(response);
                    refresh_table()
                },
                '{{__("Are You Sure For Delete This Item?")}}'
            )
        }
    </script>
@endsection