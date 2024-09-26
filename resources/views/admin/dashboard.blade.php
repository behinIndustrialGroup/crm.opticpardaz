@extends('behin-layouts.app')


@section('content')
<div class="row col-sm-12">
    <div class="alert alert-danger col-sm-2 mt-3">
        <a href="{{ route('MkhodrooProcessMaker.forms.todo') }}">
            {{ trans('کارتابل من') }}
        </a>
    </div>
</div>

@endsection
