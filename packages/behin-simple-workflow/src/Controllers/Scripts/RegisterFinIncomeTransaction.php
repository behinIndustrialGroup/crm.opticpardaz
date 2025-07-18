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

class RegisterFinIncomeTransaction extends Controller
{
    private $case;
    public function __construct($case)
    {
        $this->case = CaseController::getById($case->id);
    }

    public function execute()
    {
        $case = $this->case;
        if($case->getVariable('customer_is_connect_with_financial') == 'off')
        {
            $case_number = $this->case->number;
            $vars = $this->case->variables();
            $customer_fullname = $vars->where('key', 'customer_fullname')->first()?->value;
            $device_name = $vars->where('key', 'device_name')->first()?->value;
            $amount = $vars->where('key', 'payment_amount')->first()?->value;
            $transaction_type = Configs::where('key', 'transaction_type_income')->first()->value;
            $category = Configs::where('key', 'transaction_category_repair')->first()->value;
            $des = "هزینه دریافت شده از پرونده شماره: " . $case_number . " ";
            $des .= "بنام: ". $customer_fullname . " ";
            $des .= "دستگاه: " . $device_name;
            Transactions::create(
                [
                    'date' => date('Y-m-d'),
                    'transaction_type' => $transaction_type,
                    'amount' => $amount,
                    'description' => $des,
                    'catagory' => $category,
                    'counterparty' => $customer_fullname,
                ]    
            );
        }
        
    }
}