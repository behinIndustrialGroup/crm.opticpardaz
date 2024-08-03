<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
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

            $customer_name = $search->where('key', 'customer_fullname')->first()?->value;
            $receive_date = $search->where('key', 'receive_date')->first()?->value;
            $repairman = $search->where('key', 'repairman')->first()?->value;
            $status = $search->where('key', 'status')->first()?->value;
            $data[]= [
                'process_id' => $case->process_id,
                'case_id' => $case->case_id,
                'customer_fullname' => $customer_name,
                'receive_date' => $receive_date,
                'repairman' => $repairman,
                'status' => $status,
            ];
        }
        return [
            'data' => $data
        ];
    }
}