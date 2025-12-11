@php
    $content = json_decode($form->content);
    $formMode = in_array($inbox->status, ['done', 'doneByOther']) ? 'readonly' : null;
@endphp

@extends('behin-layouts.welcome')


@section('content')
    <form action="javascript:void(0)" method="POST" id="form" enctype="multipart/form-data" class="needs-validation"
        novalidate>
        <input type="hidden" name="inboxId" id="inboxId" value="{{ $inbox->id }}">
        <input type="hidden" name="caseId" id="caseId" value="{{ $case->id }}">
        <input type="hidden" name="taskId" id="taskId" value="{{ $task->id }}">
        <input type="hidden" name="processId" id="processId" value="{{ $process->id }}">
        @include('SimpleWorkflowView::Custom.Form.' . $form->id, [
            'form' => $form,
            'task' => $task,
            'case' => $case,
            'inbox' => $inbox,
            'variables' => $variables,
            'process' => $process,
            'mode' => $formMode,
        ])
    </form>
@endsection
@section('script')
    <script>
        initial_view()

        function createCaseNumberAndSave() {
            var form = $('#form')[0];
            var fd = new FormData(form);
            send_ajax_formdata_request(
                '{{ route('simpleWorkflow.routing.createCaseNumberAndSave') }}',
                fd,
                function(response) {
                    console.log(response);
                    if (response.status == 200) {
                        show_message(response.msg)
                        window.location.reload();
                    } else {
                        show_error(response.msg);
                    }
                }
            )
        }

        function saveForm() {
            if ($('.view-model-update-btn').length > 0) {
                $('.view-model-update-btn').click()
            }
            var form = $('#form')[0];
            var fd = new FormData(form);
            send_ajax_formdata_request(
                '{{ route('simpleWorkflow.routing.save') }}',
                fd,
                function(response) {
                    console.log(response);
                    if (response.status == 200) {
                        show_message(response.msg)
                        window.location.reload();
                    } else {
                        show_error(response.msg);
                    }
                }
            )
        }

        function saveAndNextForm() {
            if ($('.view-model-update-btn').length > 0) {
                $('.view-model-update-btn').click()
            }
            var form = $('#form')[0];
            var fd = new FormData(form);
            send_ajax_formdata_request(
                '{{ route('simpleWorkflow.routing.saveAndNext') }}',
                fd,
                function(response) {
                    console.log(response);
                    if (response.status == 200) {
                        if (response.url) {
                            window.location.href = response.url;
                        } else {
                            show_message(response.msg)
                            window.location.href = '{{ route('simpleWorkflow.inbox.index') }}';
                        }
                    } else {
                        show_error(response.msg);
                    }
                }
            )
        }

        function showJumpModal(task_id) {
            send_ajax_get_request(
                '{{ route('simpleWorkflow.task-jump.show', [$task->id, $inbox->id, $case->id, $process->id]) }}',
                function(response) {
                    open_admin_modal_with_data(response, '')
                }
            )
        }

        @if (in_array($inbox->status, ['done', 'doneByOther']))
            $(document).ready(function() {
                var form = $('#form');
                form.find('input, select, textarea, button').prop('disabled', true);
                form.find('a').removeAttr('href').css('pointer-events', 'none');
            });
        @endif
    </script>
@endsection
