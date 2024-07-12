<?php

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DynaFormController extends Controller
{

    function get(Request $r)
    {
        $accessToken = AuthController::getAccessToken();
        if($r->taskStatus === "UNASSIGNED"){
            ClaimCaseController::claim($r->caseId);
        }
        $variable_values = (new GetCaseVarsController())->getByCaseId($r->caseId, $accessToken);
        $steps = StepController::list($r->processId, $r->taskId, $accessToken);
        foreach($steps as $step){
            if($step->step_type_obj === "DYNAFORM"){
                $dynaform = $step->step_uid_obj;
            }
            $triggers = $step->triggers;
            foreach($triggers as $trigger){
                if($trigger->st_type === "BEFORE"){
                    TriggerController::excute($trigger->tri_uid, $r->caseId, $accessToken);
                }
            }
        }
        if (!$dynaform) {
            return response("شناسه فرم پیدا نشد", 400);
        }
        // $variables = VariableController::getByProcessId($r->processId);
        return view("PMViews::dynamic-forms.main-form")->with([
            'html' => DynaFormController::getHtml(
                $r->processId, 
                $r->caseId, 
                $dynaform, 
                $r->processTitle, 
                $r->caseTitle, 
                $variable_values, 
                $accessToken
            ),
            // 'vars' => $variables,
            'variable_values' => $variable_values,
            // 'input_docs' => InputDocController::list($r->appUid),
            'processId' => $r->processId,
            'processTitle' => $r->processTitle,
            'caseId' => $r->caseId,
            'taskId' => $r->taskId,
            'caseTitle' => $r->caseTitle,
            'delIndex' => $r->delIndex,
        ]);
    }

    public static function getJson($processId, $dynaId)
    {
        $accessToken = AuthController::getAccessToken();
        $json =  CurlRequestController::send(
            $accessToken,
            "/api/1.0/workflow/project/$processId/dynaform/$dynaId"
        );
        return $json;
    }

    public static function getHtml($processId, $caseId, $dynaId, $processTitle, $caseTitle, $variable_values, $accessToken = null)
    {   
        $json =  CurlRequestController::send(
            $accessToken,
            "/api/1.0/workflow/project/$processId/dynaform/$dynaId"
        );
        $content = json_decode($json->dyn_content);
        $task_title = $content->name;
        $fields = $content->items[0]->items;
        $scripts[] = self::getFormScriptCode($content->items[0]->script);
        $local_fields = GetCaseVarsController::getVarsFromLocal($processId, $caseId);
        // $docs = collect(InputDocController::list($caseId));
        echo "<form action='javascript:void(0)' id='main-form' enctype='multipart/form-data'>";
        echo "<div class='row p-1' style='color: white; background: darkolivegreen; margin: 0; border-bottom: solid 1px black'>";
            echo "<div class='col-sm-9'>";
                echo "<h4>" . trans("Case") . ": " . str_replace('"', "", $caseTitle) . " </h4>";
                echo "<h5>" . trans('Task') . ": $task_title </h5>";
                echo "<h6>" . trans("Process") . ": $processTitle</h6>";
            echo "</div>";
            echo "<div class='col-sm-3'>";
                echo "<button type='button' style='color: white; float:left; flex: auto; text-align: left' class='close' data-dismiss='modal'
                            aria-hidden='true'>&times;</button>";
            echo "</div>";
        echo "</div>";
        foreach ($fields as $rows) {
            echo "<div class='row p-2' style='margin-bottom: 10px'>";
            foreach ($rows as $field) {
                if($field->type === 'form'){
                    $scripts[] = self::getFormScriptCode($field->script);
                    $parent_mode = $field->mode;
                    foreach($field->items as $subFormRows){
                        echo "<div class='row col-sm-12' style='margin-bottom: 10px'>";
                        foreach($subFormRows as $field){
                            self::createField($field, $local_fields, $parent_mode);
                        }
                        echo "</div>";
                    }
                }else{
                    $parent_mode = isset($field->mode) ? $field->mode : '';

                    self::createField($field, $local_fields, $parent_mode);
                }
            }
            echo "</div>";
        }
        echo '</form>';
        foreach($scripts as $script){
        echo "<script>$script</script>";
        }
        if (config('pm_config.debug')){
            $data = json_encode($content);
            echo "<script>console.log($data)</script>";
        }
        
    }

    public static function createField($field, $local_fields, $parent_mode){
        $field_name = isset($field->name) ? $field->name : '';
        // $field_value = isset($variable_values->$field_name) ? $variable_values->$field_name : '';
        $field_value = $local_fields->where('key', $field_name)->first()?->value;
        $field_required = isset($field->required) and $field->required ? 'required' : '';
        if (isset($field->mode)) {
            switch ($field->mode) {
                case "parent":
                    if($parent_mode === 'parent'){
                        $field_mode = '';
                    }else{
                        $field_mode = $parent_mode;
                    }
                    break;
                case "view":
                    $field_mode = "readonly";
                    break;
                case "disabled":
                    $field_mode = "disabled";
                    break;
                default:
                    $field_mode = "";
                    break;
            }
        } else {
            $field_mode = "";
        }
        if (isset($field->type)) {
            if ($field->type == "title") {
                echo  "<div class='col-sm-$field->colSpan'>";
                echo  "<h5>$field->label</h5>";
                echo  "</div>";
            }
            if ($field->type == "hidden") {
                echo  "<div class='col-sm-$field->colSpan'>";
                echo  "<input type='hidden' name='$field->name' id='$field->name' class='form-control' value='$field_value' >";
                echo  "</div>";
            }
            if ($field->type == "text") {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": <input type='text' name='$field->name' id='$field->name' class='form-control' value='$field_value' placeholder='$field->placeholder' $field_required $field_mode>";
                echo  "</div>";
            }
            if ($field->type == "datetime") {
                if($field_value){
                    $date = new SDate();
                    // $field_value = $date->toGrDate($field_value);
                }
                
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": <input type='text' name='$field->name' id='$field->name' class='form-control persian-date' value='$field_value' $field_required $field_mode>";
                echo  "</div>";
            }
            if ($field->type == "textarea") {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": <textarea name='$field->name' id='$field->name' rows='$field->rows' class='form-control' $field_required $field_mode>$field_value</textarea>";
                echo  "</div>";
            }
            if ($field->type == 'radio') {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": ";
                echo "<div>";
                foreach ($field->options as $opt) {
                    $check = $field_value == $opt->value ? 'checked' : '';
                    $field_mode = $field_mode ? 'disabled' : '';
                    echo  "<input type='radio' value='$opt->value' name='$field->name' $check $field_mode>$opt->label <br>";
                }
                echo "</div>";
                echo  "</div>";
            }
            if ($field->type == 'checkbox') {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                $check = $field_value == 'on' ? 'checked' : '';
                $field_mode = $field_mode ? 'disabled' : '';
                echo  "<input type='checkbox' name='$field->name' $check $field_mode>". trans($field->label) . "<br>";
                echo  "<input type='hidden' name='$field->name' value='$field_value'>";

                echo  "</div>";
            }
            if ($field->type == 'dropdown') {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": ";
                echo "<div class='form-group'>";
                echo "<select name='$field->name' id='$field->name' class='form-control select2' $field_required $field_mode>";
                foreach ($field->options as $opt) {
                    $selected = $field_value == $opt->value ? 'selected' : '';
                    echo  "<option value='$opt->value' name='$field->name' $selected>$opt->label</option>";
                }
                if($field->sql){
                    $options = DB::select($field->sql);
                    foreach ($options as $opt) {
                        $selected = $field_value == $opt->value ? 'selected' : '';
                        echo  "<option value='$opt->value' name='$field->name' $selected>$opt->label</option>";
                    }
                    $field->sql_options = $options;
                }
                
                echo "</select>";
                echo "</div>";
                echo  "</div>";
            }
            if ($field->type == 'file') {
                // echo "<pre>";
                // print_r($field);
                // echo "<pre>";
                // echo  "<div class='col-sm-$field->colSpan'>";
                // echo  "$field->label: ";
                // echo "<div style='text-align: center'>";
                // if ($field_value != '') {
                //     // print_r($field_value);
                //     $values = json_decode($field_value);
                //     // print_r($values);
                //     $doc = $docs->where('app_doc_uid', $values[0])->first();
                //     if($field->mode != 'view'){
                //         // echo "<label for='$field->inp_doc_uid' class='btn'>Select Image</label>";
                //         echo "<input id='$field->inp_doc_uid' type='file' name='$field->name-$field->inp_doc_uid' class='form-control' >";
                //     }
                //     echo "<a href='https://pmaker.altfuel.ir/sysworkflow/en/neoclassic/$doc?->app_doc_link' >$doc?->app_doc_filename</a>";
                    

                // } else {
                //     if($field->mode != 'view'){
                //         echo "<input id='$field->inp_doc_uid' type='file' name='$field->name-$field->inp_doc_uid' class='form-control'>";
                //     }
                // }
                // echo "</div>";
                // echo "</div>";

            }
            if ($field->type == 'multipleFile') {
                echo  "<div class='col-sm-$field->colSpan' id='$field->name-div'>";
                echo  trans($field->label) . ": ";
                echo "<div style='text-align: center'>";

                $field_rows = $local_fields->where('key', $field->name);
                foreach($field_rows as $field_row){
                    echo "<a target='_blank' href='". url("public/$field_row->value") ."' >$field->name</a> | ";
                    echo "<i class='fa fa-trash' onclick='delete_doc(
                        $field_row->id
                    )'></i> <br>";
                }
                if($field_mode === 'edit'){
                    echo "<input id='$field->name' multiple='multiple' type='file' name='$field->name[]' class='form-control' >";
                }

                
                echo "</div>";
                echo "</div>";

            }
        }
    }

    public static function getFormScriptCode($script){
        return is_string($script) ? '' : $script->code;
    }
}


