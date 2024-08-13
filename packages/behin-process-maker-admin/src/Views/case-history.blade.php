{{-- @extends(config('pm_config.layout_name')) --}}

{{-- @section('content') --}}
    <div class="col-sm-12" style="height: 10px;"></div>
    <div class="card row table-responsive" style="padding: 5px">
        <table class="table table-striped " id="list">
            <thead>
                <tr>
                    <th>{{__('del_index')}}</th>
                    <th>{{__('del_init_date')}}</th>
                    <th>{{__('tas_title')}}</th>
                    <th>{{__('status')}}</th>
                    <th>{{__('del_finish_date')}}</th>
                    <th>{{__('usr_firstname')}}</th>
                    <th>{{__('usr_lastname')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{$item['del_index']}}</td>
                        <td>{{$item['del_init_date']}}</td>
                        <td>{{$item['tas_title']}}</td>
                        <td>{{$item['status']}}</td>
                        <td>{{$item['del_finish_date']}}</td>
                        <td>{{$item['usr_firstname']}}</td>
                        <td>{{$item['usr_lastname']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
