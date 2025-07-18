<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Core\Inbox;
use Behin\SimpleWorkflow\Controllers\Core\InboxController;
use Behin\SimpleWorkflow\Controllers\Core\RoutingController;
use Illuminate\Http\Request;
use Behin\SimpleWorkflow\Models\Entities\Customers;
use Behin\SimpleWorkflow\Models\Entities\Case_customer;

class StoreCustomerInfo extends Controller
{
    protected $case;
    public function __construct($case) {
        $this->case = $case;
        
    }

    public function execute()
    {
        $case = $this->case;
        $caseCustomer = Case_customer::where('case_id', $case->id)->first();
        $mobile = $caseCustomer->mobile;
        $customer = Customers::where('mobile', $mobile)->first();
        if(!$customer){
            $customer = new Customers();
            $customer->fullname = $caseCustomer->fullname;
            $customer->national_id = $caseCustomer->national_id;
            $customer->mobile = $caseCustomer->mobile;
            $customer->address = $caseCustomer->address;
            $customer->save();
        }
        
    }

}