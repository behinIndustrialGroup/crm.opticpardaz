@extends('behin-layouts.app')

@section('title')
    {{ trans('fields.Edit Records') }}
@endsection

@section('content')
<div class="container card p-3 table-responsive">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <a href="{{ route('simpleWorkflow.entities.createRecord', $entity->id) }}" class="btn btn-primary mb-3">{{ trans('fields.Add Record') }}</a>
    <table class="table table-strpped" id="recordsTable">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th class="text-left">{{ trans('fields.' . $column) }}</th>
                @endforeach
                <th>{{ trans('fields.Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $record)
            <tr>
                @foreach($columns as $column)
                    <td class="text-left">{{ $record->$column }}</td>
                @endforeach
                <td>
                    <a href="{{ route('simpleWorkflow.entities.editRecord', [$entity->id, $record->id]) }}">{{ trans('fields.Edit') }}</a>
                    <form action="{{ route('simpleWorkflow.entities.deleteRecord', [$entity->id, $record->id]) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-link p-0 ms-2">{{ trans('fields.Delete') }}</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@section('script')
<script>
    $('#recordsTable').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Persian.json"
        }
    });
</script>
@endsection
