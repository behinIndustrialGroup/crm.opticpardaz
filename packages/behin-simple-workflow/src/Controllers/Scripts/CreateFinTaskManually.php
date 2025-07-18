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

class CreateFinTaskManually extends Controller
{
    protected $case;
    public function __construct($case) {
        $this->case = $case;
        
    }

    public function execute()
    {
        $case = $this->case;
        $request = new Request([
            'caseId' => $case->id,
            'processId' => $case->process_id,
            'next_task_id' => 'b88d5627-6d30-48f9-8363-384489b96059'
        ]);
        
        return RoutingController::jumpTo($request);
        
    }

}