<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\PMUserController;
use BehinProcessMaker\Models\PmVars;

class AllCasesController extends Controller
{
    public function form(){
        return view('PMAdminViews::list');
    }

    public static function all(){
        $cases = PmVars::groupBy('process_id', 'case_id')->get();
        $data = [];
        foreach($cases as $case){
            $search = PmVars::where('case_id', $case->case_id);

            $customer_name = PmVars::where('case_id', $case->case_id)->where('key', 'customer_fullname')->first()?->value;
            $receive_date = PmVars::where('case_id', $case->case_id)->where('key', 'receive_date')->first()?->value;
            $repairman_id = PmVars::where('case_id', $case->case_id)->where('key', 'repairman')->first()?->value;
            $repairman = PMUserController::getUserByPmUserId($repairman_id);
            $status = PmVars::where('case_id', $case->case_id)->where('key', 'status')->first()?->value;
            $caseinfo = CaseInfoController::get($case->case_id);
            $data[]= [
                'process_id' => $case->process_id,
                'case_id' => $case->case_id,
                'customer_fullname' => $customer_name,
                'receive_date' => $receive_date,
                'repairman' => $repairman?->name,
                'caseInfo' => $caseinfo,
                'status' => $status,
            ];
        }
        return [
            'data' => $data
        ];
    }
}