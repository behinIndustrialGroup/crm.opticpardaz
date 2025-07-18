<?php

namespace Behin\SimpleWorkflow\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Core\Inbox;
use Behin\SimpleWorkflow\Controllers\Core\InboxController;
use Illuminate\Http\Request;

class FinTaskIsExist extends Controller
{
    protected $case;
    public function __construct($case) {
        $this->case = $case;
        
    }

    public function execute()
    {
        $case = $this->case;
        $caseId = $case->id;
        
        $inbox = Inbox::where('case_id', $caseId)->pluck('task_id')->toArray();
        
        $finTasks = [
            "b88d5627-6d30-48f9-8363-384489b96059",
            "6f75a584-ef3a-4650-a9ab-25d01d69e40f",
            "40408498-53d1-475c-bc95-ad8e6473353e",
            "971c04db-5290-4a7a-af05-3ab9e201b30a",
            "c25dae20-370b-4c54-b90b-36f6adc483ad",
            "6454b7a5-d7fc-4db6-818a-833ea65d656a",
            "c0575363-3713-429d-888e-d87dbba3d6c1",
            "24728e28-cc0b-41e1-88f0-a1b1810eaed2"
        ];
        $intersect = array_intersect($inbox,$finTasks);
        $intersect = array_values($intersect);
        
        if(count($intersect)){
            VariableController::save(
                $case->process_id,
                $caseId,
                'fin_task_is_exist',
                'yes'
            );
        }else{
            VariableController::save(
                $case->process_id,
                $caseId,
                'fin_task_is_exist',
                'no'
            );
        }
        
        
    }

}