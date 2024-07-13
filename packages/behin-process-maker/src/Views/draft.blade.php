@extends(config('pm_config.layout_name'))

@section('content')
    <div class="col-sm-12" style="height: 10px;"></div>
    <div class="card row table-responsive" style="padding: 5px">
        <table class="table table-striped " id="draft-list">
            <thead>
                <tr>
                    {{-- <th>{{__('Id')}}</th> --}}
                    <th>{{__('Number')}}</th>
                    <th>{{__('Process Name')}}</th>
                    <th>{{__('Task Title')}}</th>
                    <th>{{__('Case')}}</th>
                    <th>{{__('Status')}}</th>
                    <th>{{__('Send By')}}</th>
                    <th style="text-align: center; direction: ltr">{{__('Send Date')}}</th>
                    <th style="text-align: center; direction: ltr">{{__('Delay/Deadline')}}</th>
                    <th>{{__('Action')}}</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

@section('script')
    <script>
        $.get('{{ route("MkhodrooProcessMaker.api.draft") }}', function(r){
            console.log(r);
        })
        var table = create_datatable(
            'draft-list',
            '{{ route("MkhodrooProcessMaker.api.draft") }}',
            [
                // {data : 'APP_UID', render: function(APP_UID){return APP_UID.substr(APP_UID.length - 8)}},
                {data : 'APP_NUMBER'},
                {data : 'PRO_TITLE'},
                {data : 'TAS_TITLE'},
                {data : 'DEL_TITLE'},
                {data : 'TAS_STATUS', render: function(data){
                    if(data == 'ON_TIME'){
                        return '{{trans("ON_TIME")}}';
                    }else if(data == 'OVERDUE'){
                        return '{{trans("OVERDUE")}}';
                    }else{
                        return data;
                    }
                }},
                {data : 'SEND_BY_INFO', render: function(SEND_BY_INFO){
                    if(SEND_BY_INFO.user_tooltip.usr_firstname){
                        user = SEND_BY_INFO.user_tooltip;
                        name = user.usr_firstname + ' ' + user.usr_lastname 
                        return name.substring(0,15);
                    }else{
                        return '-';
                    }
                }},
                {data : 'DEL_DELEGATE_DATE', render: function(DEL_DELEGATE_DATE){ 
                    date = DEL_DELEGATE_DATE.split(" ")[0]
                    time = DEL_DELEGATE_DATE.split(" ")[1]
                    return `<span style="float: left; direction: ltr">${date} ${time}</span>`; 
                }},
                {data : 'DELAY' , render: function(DELAY, type, row){ 
                    delay_day = DELAY.split(" ")[1]
                    delay_h = DELAY.split(" ")[3]
                    delay_m = DELAY.split(" ")[5]
                    delay_s = DELAY.split(" ")[7]
                    return `<span style="float: left; direction: ltr; color: ${row.TAS_COLOR_LABEL}">${delay_day}d  ${delay_h}h ${delay_m}m ${delay_s}s</span>`; 
                }},
                {data : 'APP_UID', render: function(APP_UID){return  `<i class='fa fa-trash bg-red' onclick="delete_case('${APP_UID}')"></i>`; }},
            ],
            function(row){
                $(row).css('cursor', 'pointer')
            }
        );
        table.on('dblclick', 'tr', function(){
            var data = table.row( this ).data();
            console.log(data);
            var fd = new FormData();
            fd.append('appUid', data.APP_UID);
            fd.append('processTitle', data.PRO_TITLE);
            fd.append('taskId', data.TAS_UID);
            fd.append('caseId', data.APP_UID);
            fd.append('caseTitle', data.DEL_TITLE);
            fd.append('processId', data.PRO_UID);
            fd.append('delIndex', data.DEL_INDEX);
            url = "{{ route('MkhodrooProcessMaker.api.getCaseDynaForm') }}";
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