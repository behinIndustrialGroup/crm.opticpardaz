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

class StoreExpenseInTransaction extends Controller
{
    private $case;
    public function __construct()
    {
        // $this->case = CaseController::getById($case->id);
    }

    public function execute(Request $request = null)
    {
        $transaction_type = Configs::where('key', 'transaction_type_expense')->first()->value;
        $caseId = $request->caseId;
        $case = CaseController::getById($caseId);
        $category = $request->transaction_category;
        $description = $request->transaction_description;
        $amount = $request->transaction_amount;
        $counterparty = $request->transaction_counterparty;
        $note = $request->transaction_note;
        
        if(!$caseId){
            return "شناسه پرونده خالیست";
        }
        if(!$category){
            return "دسته بندی خالیست";
        }
        if(!$description){
            return "توضیحات خالیست";
        }
        if(!$amount){
            return "مقدار خالیست";
        }
        if(!$counterparty){
            return "طرف حساب خالیست";
        }
        if(!is_numeric($amount)){
            return "هزینه به صورت عدد انگلیسی وارد شود";
        }
        
        
        Transactions::create(
            [
                'date' => date('Y-m-d'),
                'transaction_type' => $transaction_type,
                'amount' => $amount,
                'description' => $description,
                'category' => $category,
                'counterparty' => $counterparty,
                'case_id' => $caseId
            ]    
        );
    }
}