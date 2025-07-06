<?php

namespace Behin\SimpleWorkflowReport\Controllers\Scripts;

use App\Http\Controllers\Controller;
use Behin\SimpleWorkflow\Controllers\Core\CaseController;
use Behin\SimpleWorkflow\Controllers\Core\VariableController;
use Behin\SimpleWorkflow\Models\Core\Process;
use Behin\SimpleWorkflow\Models\Core\Task;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflow\Models\Entities\Customers;
use Behin\SimpleWorkflow\Models\Entities\Device_repair;
use Behin\SimpleWorkflow\Models\Entities\Devices;
use Behin\SimpleWorkflow\Models\Entities\Repair_cost;
use Behin\SimpleWorkflow\Models\Entities\Repair_incomes;
use Illuminate\Http\Request;

class OPPAReportController extends Controller
{
    public function show($oppa_report)
    {
        $case = CaseController::getById($oppa_report);
        $customer = [
            'name' => $case->getVariable('customer_fullname'),
            'national_id' => $case->getVariable('customer_fullname'),
            'mobile' => $case->getVariable('customer_mobile'),
            'address' => $case->getVariable('customer_address'),
        ];
        $device = Devices::where('case_number', $case->number)->first();
        $repairs = Device_repair::where('case_number', $case->number)->get();
        $repairCost = Repair_cost::where('case_number', $case->number)->first();
        $repairIncomes = Repair_incomes::where('case_number', $case->number)->get();
        return view('SimpleWorkflowReportView::Core.RepairInOPPA.show', compact('case', 'customer', 'device', 'repairs', 'repairCost', 'repairIncomes'));
    }

    public function update(Request $request, $oppa_report)
    {
        $data = $request->except('_token', '_method');
        $case = CaseController::getById($oppa_report);
        if ($request->form_type == 'device') {
            $device = Devices::where('case_id', $case->id)->first();
            $device->update([
                'name' => $request->device_name,
                'brand' => $request->device_brand,
                'power' => $request->device_power,
                'serial' => $request->device_serial_no,
                'plaque_pic' => $request->device_plaque_pic,
                'initial_pic' => $request->device_initial_pic,

            ]);

            return redirect()->back()->with('success', trans('fields.Device updated successfully'));
        }
        if ($request->form_type == 'repair') {
            $repair = Device_repair::where('id', $request->repair_id)->first();
            $repair->update([
                'repairman_assitant' => $request->repairman_assitant,
                'repair_start_timestamp' => $request->repair_start_date,
                'repair_type' => $request->repair_type,
                'repair_subtype' => $request->repair_subtype,
                'repair_report' => $request->repair_report,
            ]);
            return redirect()->back()->with('success', trans('fields.Repair updated successfully'));
        }
    }
}
