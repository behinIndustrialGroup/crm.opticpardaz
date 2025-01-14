<?php

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Core\Variable;
use BehinFileControl\Controllers\FileController;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SaveVarsController extends Controller
{
    public static function save($process_id, $case_id, $key, $value) {
        PmVars::updateOrCreate(
            [
                'process_id' => $process_id,
                'case_id' => $case_id,
                'key' => $key
            ],
            [
                'value' => $value
            ]
            );
        $case_number = PmVars::where('process_id', $process_id)->where('case_id', $case_id)->where('key', 'case_number')->first()?->value;
        $caseId_in_simpleWorkflow = Variable::where('key', $case_number)->first()?->case_id;
        if(!$caseId_in_simpleWorkflow){
            $creator = PmVars::where('case_id', $case_id)->where('key', 'crm_user_creator')->first()?->value;
            $creator = $creator ? $creator : 1;
            $name = PmVars::where('case_id', $case_id)->where('key', 'device_serial_no')->first()?->value;
            $name = 'سریال نامبر: ' . $name;
            $caseId_in_simpleWorkflow = Cases::create([
                'process_id' => '879e001c-59d5-4afb-958c-15ec7ff269d1',
                'number' => $case_number,
                'name' => $name,
                'creator' => $creator
            ]);
            $caseId_in_simpleWorkflow = $caseId_in_simpleWorkflow->id;
            $vars = PmVars::where('case_id', $case_id)->get()->each(function ($row) use ($caseId_in_simpleWorkflow) {
                $row->process_id = '879e001c-59d5-4afb-958c-15ec7ff269d1';
                $row->case_id = $caseId_in_simpleWorkflow;
            })->toArray();
            foreach ($vars as $var) {
                Variable::create($var);
            }
        }
        $processId_in_simpleWorkflow = Variable::where('key', $case_number)->first()?->process_id;
        if($caseId_in_simpleWorkflow){
            VariableController::save($processId_in_simpleWorkflow, $caseId_in_simpleWorkflow, $key, $value);
        }
    }


    public static function saveDoc($process_id, $case_id, $key, $value) {
        $value = FileController::store(
            $value,
            'pm-docs'
        );
        if($value['status'] == 200){
            PmVars::create(
                [
                    'process_id' => $process_id,
                    'case_id' => $case_id,
                    'key' => $key,
                    'value' => $value['dir']
                ]
            );
            return ;
        }
        return response($value['message'], $value['status']);
        
    }
}
