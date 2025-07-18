<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Illuminate\Http\Request;

class PreInvoiceValidation extends Controller
{
    protected $case;
    public function __construct($case) {
        $this->case = $case;
        
    }

    public function execute()
    {
        $case = $this->case;
        if($case->getVariable('customer_is_connect_with_financial') == 'off')
        {
            if(!$case->getVariable('pre_invoice')){
                return "فایل پیش فاکتور را بارگزاری نمایید";
            }
            if(!$case->getVariable('pre_invoice_has_been_sended_to_customer')){
                return "گزینه پیش فاکتور برای مشتری ارسال شد را تعیین کنید";
            }
        }
        VariableController::save(
            $case->process_id,
            $case->id,
            'customer_is_connect_with_financial',
            'off'
        );
        
    }

}