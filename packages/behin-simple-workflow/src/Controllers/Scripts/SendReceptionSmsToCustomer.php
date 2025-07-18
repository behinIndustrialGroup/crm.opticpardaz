<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\Sms\Controllers\SmsController;
use Illuminate\Http\Request;
use Behin\SimpleWorkflow\Models\Entities\Case_customer;

class SendReceptionSmsToCustomer extends Controller
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
        $caseCustomer = Case_customer::where('case_id', $case->id)->first();
        $customerMobile = $caseCustomer->mobile;
        $customerMobile = convertPersianToEnglish($customerMobile);
        $customerFullname = $caseCustomer->fullname;
        // return $customerMobile;
        if(strlen($customerMobile) != 11){
            return "شماره موبایل باید 11 رقم باشد";
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
            $result = SmsController::sendByTemp($customerMobile, 102368, $params);
            // return json_encode($result);
        }
    }
}