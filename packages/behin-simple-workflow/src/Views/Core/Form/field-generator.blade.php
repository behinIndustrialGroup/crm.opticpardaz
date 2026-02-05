@php
    $fieldLabel = trans('SimpleWorkflowLang::fields.' . $fieldName);
    $fieldDetails = getFieldDetailsByName($fieldName);

    $rawAttributes = $fieldDetails ? $fieldDetails->attributes : null;
    $decodedAttributes = $rawAttributes ? json_decode($rawAttributes) : null;
    $fieldAttributes = is_object($decodedAttributes) ? $decodedAttributes : new stdClass();

    $fieldType = isset($fieldDetails->type) ? $fieldDetails->type : null;
    $fieldStyle = isset($fieldAttributes->style) ? $fieldAttributes->style : null;
    $fieldScript = isset($fieldAttributes->script) ? $fieldAttributes->script : null;
    $fieldPlaceholder = isset($fieldAttributes->placeholder) ? $fieldAttributes->placeholder : null;
    $fieldOptions = isset($fieldAttributes->options) ? $fieldAttributes->options : null;
    $fieldQuery = isset($fieldAttributes->query) ? $fieldAttributes->query : null;
    $fieldDatalist = isset($fieldAttributes->datalist_from_database) ? $fieldAttributes->datalist_from_database : null;
    $fieldClass = isset($fieldAttributes->class) ? $fieldAttributes->class : 'form-control';
@endphp
@if ($fieldType === 'title')
    {!! Form::title($fieldId, [
        'value' => $fieldValue,
        'class' => '',
        'id' => $fieldId,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'hidden')
    {!! Form::hidden($fieldId, [
        'value' => $fieldValue,
        'class' => '',
        'id' => $fieldId,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'help')
    {!! Form::help($fieldId, [
        'options' => $fieldOptions,
        'class' => '',
        'id' => $fieldDetails->id ?? $fieldId,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
    @if ($fieldType === 'location')
        @php
            $defaultLat = null;
            $defaultLng = null;

            if (isset($variables)) {
                $latVariable = $variables->where('key', $fieldName . '_lat')->first();
                $lngVariable = $variables->where('key', $fieldName . '_lng')->first();

                $defaultLat = $latVariable ? $latVariable->value : null;
                $defaultLng = $lngVariable ? $lngVariable->value : null;
            }
        @endphp
    {!! Form::location($fieldId, [
        'value' => $fieldValue,
        'class' => '',
        'id' => $fieldId,
        'required' => $required,
        'readonly' => $readOnly,
        'defaultZoom' => 13,
        'defaultLat' => $defaultLat,
        'defaultLng' => $defaultLng,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'string')
    {!! Form::text($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
        'datalist_from_database' => $fieldDatalist,
    ]) !!}
@endif
@if ($fieldType === 'checkbox')
    {!! Form::checkbox($fieldId, [
        'value' => $fieldValue,
        'class' => '',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'text')
    {!! Form::textarea($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'date')
    {!! Form::date($fieldId, [
        'value' => $fieldValue,
        'altValue' =>   $fieldValueAlt,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'time')
    {!! Form::time($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control timepicker',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'datetime')
    {!! Form::datetime($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'select')
    {!! Form::select($fieldId, is_string($fieldOptions) ? $fieldOptions : null, [
        'value' => $fieldValue,
        'query' => is_string($fieldQuery) ? $fieldQuery : null,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'select-simple')
    {!! Form::selectSimple($fieldId, is_string($fieldOptions) ? $fieldOptions : null, [
        'value' => $fieldValue,
        'query' => is_string($fieldQuery) ? $fieldQuery : null,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'searchable-input')
    {!! Form::searchableInput($fieldId, [
        'value' => $fieldValue,
        'endpoint' => isset($fieldAttributes->endpoint) && is_string($fieldAttributes->endpoint)
            ? $fieldAttributes->endpoint
            : null,
        'minChars' => isset($fieldAttributes->minChars) ? $fieldAttributes->minChars : null,
        'limit' => isset($fieldAttributes->limit) ? $fieldAttributes->limit : null,
        'initial_label' => $fieldAttributes->initial_label ?? ($fieldAttributes->initialLabel ?? null),
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'select-multiple')
    {!! Form::selectMultiple($fieldId, is_string($fieldOptions) ? $fieldOptions : null, [
        'value' => json_decode($fieldValue),
        'query' => is_string($fieldQuery) ? $fieldQuery : null,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'file')
    {{-- @php
        $fieldValues = isset($variables) ? $variables->where('key', $field->fieldName)->pluck('value') : [];
    @endphp --}}
    {!! Form::file($fieldId, [
        'value' => $fieldValue ?? [],
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'signature')
    {!! Form::signature($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
        'datalist_from_database' => $fieldDatalist,
    ]) !!}
@endif
@if ($fieldType === 'entity')
    {!! Form::entity($fieldId, [
        'columns' => isset($fieldAttributes->columns) && is_string($fieldAttributes->columns) ? $fieldAttributes->columns : null,
        'query' => is_string($fieldQuery) ? $fieldQuery : null,
        'class' => 'form-control',
        'id' => $fieldAttributes->id ?? null,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'button')
    {!! Form::button($fieldName, [
        'class' => $fieldClass,
        'id' => $fieldAttributes->id ?? $fieldName,
        'style' => $fieldStyle,
        'script' => $fieldScript,
    ]) !!}
@endif
@if ($fieldType === 'view-model')
    {!! Form::viewModel($fieldId, [
        'class' => $fieldClass,
        'id' => $fieldId,
        'view_model_id' => $fieldAttributes->view_model_id ?? null,
        'style' => $fieldStyle,
    ]) !!}
@endif

@if ($fieldType === 'formatted-digit')
    {!! Form::formattedDigit($fieldId, [
        'value' => $fieldValue,
        'class' => 'form-control',
        'id' => $fieldId,
        'placeholder' => $fieldPlaceholder,
        'required' => $required,
        'readonly' => $readOnly,
        'style' => $fieldStyle,
        'script' => $fieldScript,
        'datalist_from_database' => $fieldDatalist,
    ]) !!}
@endif
