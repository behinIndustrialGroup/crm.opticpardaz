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
use Behin\SimpleWorkflow\Models\Entities\Warehouses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoreWarehouse extends Controller
{
    private $case;
    public function __construct($case)
    {
        $this->case = CaseController::getById($case->id);
    }

    public function execute()
    {
        $variables = $this->case->variables();
        $name = $variables->where('key', 'warehouse_name')->first()?->value;
        $manager = $variables->where('key', 'warehouse_manager')->first()?->value;
        $phone = $variables->where('key', 'warehouse_phone')->first()?->value;
        $address = $variables->where('key', 'warehouse_address')->first()?->value;
        $capacity = $variables->where('key', 'warehouse_capacity')->first()?->value;
        Warehouses::create([
                'name' => $name,
                'manager' => $manager,
                'phone' => $phone,
                'address' => $address,
                'capacity' => $capacity
            ]);
    }
}