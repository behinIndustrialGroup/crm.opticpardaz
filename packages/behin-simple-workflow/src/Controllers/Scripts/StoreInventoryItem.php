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
use Behin\SimpleWorkflow\Models\Entities\Warehouses;
use Behin\SimpleWorkflow\Models\Entities\Products;
use Behin\SimpleWorkflow\Models\Entities\Inventory_items;
use Illuminate\Support\Facades\Auth;

class StoreInventoryItem extends Controller
{
    private $case;
    public function __construct()
    {
        // $this->case = CaseController::getById($case->id);
    }

    public function execute(Request $request = null)
    {
        $caseId = $request->caseId;
        $case = CaseController::getById($caseId);
        $request->validate([
            'warehouse_id' => 'required',
            'product_name' => 'required',
            'quantity' => 'required'
        ]);
        
        $data = $request->all();
        $warehouse = Warehouses::find($request->warehouse_id);
        $product = Products::firstOrCreate([ 'name' =>  $request->product_name]);
        $data['warehouse_id'] = $warehouse->id;
        $data['warehouse_name'] = $warehouse->name;
        $data['product_id'] = $product->id;
        $data['product_name'] = $product->name;
        $data['registerer'] = Auth::id();
        
        Inventory_items::create($data);
        return response(trans("fields.Stored"));
        
        
        return Transactions::where('case_id', $caseId)->get();
    }
}