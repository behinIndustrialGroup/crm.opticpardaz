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
                    <th>{{__('RepairMan')}}</th>
                    <th>{{__('Status')}}</th>
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
                {data : 'case_id'},
                {data : 'customer_fullname'},
                {data : 'receive_date'},
                {data : 'repairman'},
                {data : 'status'}
            ],
            function(row){
                $(row).css('cursor', 'pointer')
            }
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

        function delete_case(caseId){
            url = "{{ route('MkhodrooProcessMaker.api.deleteCase', [ 'caseId' => 'caseId' ]) }}";
            url = url.replace('caseId', caseId)
            console.log(url);
            send_ajax_get_request_with_confirm(
                url,
                function(response){
                    console.log(response);
                    refresh_table()
                },
                '{{__("Are You Sure For Delete This Item?")}}'
            )
        }
    </script>
@endsection