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

class StoreNewOption extends Controller
{
    private $case;
    public function __construct($case)
    {
        $this->case = CaseController::getById($case->id);
    }

    public function execute()
    {
        $vars = $this->case->variables();
        $key = $vars->where('key', 'config_key')->first()?->value;
        $value = $vars->where('key', 'config_value')->first()?->value;
        $format = $vars->where('key', 'config_value_format_save')->first()?->value;
        if($format == 'json'){
            $value = json_encode($value);
        }
        Configs::updateOrCreate(
            [
                'key' => $key
            ],
            [
                'value' => $value
            ]);
        return "Saved";
    }
}