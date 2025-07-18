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
use Behin\SimpleWorkflow\Models\Entities\Repair_reports;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Behin\SimpleWorkflow\Models\Entities\Configs;
use Behin\SimpleWorkflow\Models\Entities\Transactions;
use Illuminate\Support\Facades\Auth;

class StoreCaseNumber extends Controller
{
    private $case;
    public function __construct()
    {
        // $this->case = CaseController::getById($case->id);
    }

    public function execute(Request $request = null)
    {
        $caseId = $request->caseId;
        $case = CaseController::getByid($caseId);
        $previousCaseId = $request->previousCaseId;
        $previousCase = CaseController::getByid($previousCaseId);
        
        $case->saveVariable('previousCaseId', $previousCaseId);
        $case->number = $previousCase->number;
        $case->parent_id = $previousCase->id;
        $case->save();
        $case->copyVariableFrom($previousCase->id);
        
        $task = TaskController::getById('7434396b-54ec-4624-840e-e7b24db73eaf'); // مرحله پذیرش در فرایند تعمیر در اپیتک پرداز
        
        $inbox = InboxController::create(
            $task->id,
            $case->id,
            Auth::id()
            );
            
        return route('simpleWorkflow.inbox.view', $inbox->id);
        
    }
}