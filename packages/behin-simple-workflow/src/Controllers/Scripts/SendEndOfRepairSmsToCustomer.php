<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\Sms\Controllers\SmsController;
use Illuminate\Http\Request;

class SendEndOfRepairSmsToCustomer extends Controller
{
    protected $case;
    public function __construct($case)
    {
        $this->case = $case;
        // return VariableController::save(
        //     $this->case->process_id, $this->case->id, 'manager', 2
        // );
    }

    public function execute()
    {
        $case = $this->case;
        $caseNumber = $this->case->number;
        $variables = $this->case->variables();
        $customerMobile = $variables->where('key', 'customer_mobile')->first()?->value;
        $customerMobile = convertPersianToEnglish($customerMobile);
        $customerFullname = $case->getVariable('customer_fullname');
        // return $customerMobile;
        if(strlen($customerMobile) != 11){
            return response("شماره موبایل باید 11 رقم باشد", 402);
        }
        if ($customerMobile) {
            $params = array(
                [
                    "name" => "NAME",
                    "value" => $customerFullname
                ],
                [
                    "name" => "CASE_NUMBER",
                    "value" => $caseNumber
                ]
            );
            // $result = SmsController::sendByTemp($customerMobile, 576602, $params);
            $result = SmsController::sendByTemp($customerMobile, 576602, $params);
            // return json_encode($result);
        }
    }
}