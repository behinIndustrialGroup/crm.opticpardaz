<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Core\Inbox;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Controllers\Core\InboxController;
use Behin\SimpleWorkflow\Controllers\Core\RoutingController;
use Illuminate\Http\Request;
use Behin\SimpleWorkflow\Models\Entities\Customers;

class GetCustomerByName extends Controller
{
    protected $case;
    public function __construct($case = null) {
        // $this->case = $case;
        
    }

    public function execute(Request $request)
    {
        $q = $request->q;
        $customers = Customers::where('fullname', 'like', "%$q%")->get();
        return $customers;
        
    }

}