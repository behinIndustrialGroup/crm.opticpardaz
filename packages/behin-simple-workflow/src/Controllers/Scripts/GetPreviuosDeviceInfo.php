<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\CaseController;
use Behin\SimpleWorkflow\Controllers\Core\InboxController;
use Behin\SimpleWorkflow\Controllers\Core\ProcessController;
use Behin\SimpleWorkflow\Controllers\Core\TaskController;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Entities\Repair_reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Behin\SimpleWorkflow\Models\Entities\Configs;
use Behin\SimpleWorkflow\Models\Entities\Transactions;

class GetPreviuosDeviceInfo extends Controller
{
    private $case;
    public function __construct()
    {
        // $this->case = CaseController::getById($case->id);
    }

    public function execute(Request $request = null)
    {
        // return $request->all();
        $caseId = $request->caseId;
        $case = CaseController::getById($caseId);
        
        $caseNumber = $case->number;
        $cases = Cases::where('number', $caseNumber)->whereNot('id', $case->id)->get();
        
        $previuosData = [];
        foreach($cases as $case){
            $repairman = getUserInfo($case->getVariable('repairman'))?->name;
            $previuosData[] = [
                'receive_date' => $case->getVariable('receive_date'),
                'repairman' => $repairman,
                'repair_report' => $case->getVariable('repair_report'),
                ];
        }
        return $previuosData;
        $rows = Variable::where('key', 'device_serial_no')->where('value', $deviceSerial)->get();
        
        $result = [];
        foreach($rows as $row){
            $result[] = [
                'case_id' => $row->case->id,
                'case_number' => $row->case->number,
                'customer_name' => $row->case->getVariable('customer_fullname'),
                'device_name' => $row->case->getVariable('device_name'),
                'receive_date' => $row->case->getVariable('receive_date'),
                ];
        }
        return $result;
    }
}