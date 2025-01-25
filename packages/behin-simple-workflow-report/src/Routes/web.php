<?php

use Behin\SimpleWorkflow\Controllers\Core\ConditionController;
use Behin\SimpleWorkflow\Controllers\Core\FieldController;
use Behin\SimpleWorkflow\Controllers\Core\FormController;
use Behin\SimpleWorkflow\Controllers\Core\InboxController;
use Behin\SimpleWorkflow\Controllers\Core\ProcessController;
use Behin\SimpleWorkflow\Controllers\Core\RoutingController;
use Behin\SimpleWorkflow\Controllers\Core\ScriptController;
use Behin\SimpleWorkflow\Controllers\Core\TaskActorController;
use Behin\SimpleWorkflow\Controllers\Core\TaskController;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflowReport\Controllers\Core\FinReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\ReportController;
use BehinProcessMaker\Models\PMVariable;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Support\Facades\Route;

Route::name('simpleWorkflowReport.')->prefix('workflow-report')->middleware(['web', 'auth'])->group(function () {
    Route::get('index', [ReportController::class, 'index'])->name('index');
    Route::resource('report', ReportController::class);
    Route::resource('fin-report', FinReportController::class);
    Route::get('total-payment', [FinReportController::class, 'totalPayment'])->name('totalPayment');
    Route::get('test', function(){
        $cases = Variable::groupBy('case_id')->get();
        echo "<table>";
        echo "<tr>";
        echo "<td>case_id</td>";
        echo "<td>device_name</td>";
        echo "<td>device_model</td>";
        echo "<td>initial_description</td>";
        echo "<td>repair_report</td>";
        echo "</tr>";
        foreach ($cases as $case) {
            $device_name = Variable::where('case_id', $case->case_id)->where('key', 'device_name')->first()?->value;
            $device_model = Variable::where('case_id', $case->case_id)->where('key', 'device_model')->first()?->value;
            $initial_description = Variable::where('case_id', $case->case_id)->where('key', 'initial_description')->first()?->value;
            $repair_report = Variable::where('case_id', $case->case_id)->where('key', 'repair_report')->first()?->value;
            echo "<tr>";
            echo "<td>$case->case_id</td>";
            echo "<td>$device_name</td>";
            echo "<td>$device_model</td>";
            echo "<td>$initial_description</td>";
            echo "<td>$repair_report</td>";
            echo "</tr>";
        }
        echo "</table>";
    });
    Route::get('import', function () {
        $cases = PmVars::groupBy('case_id')->get();
        foreach ($cases as $case) {
            $number = PmVars::where('case_id', $case->case_id)->where('key', 'case_number')->first()?->value;
            $number = $number ? $number : 1;
            $creator = PmVars::where('case_id', $case->case_id)->where('key', 'crm_user_creator')->first()?->value;
            $creator = $creator ? $creator : 1;
            $name = PmVars::where('case_id', $case->case_id)->where('key', 'device_serial_no')->first()?->value;
            $name = 'سریال نامبر: ' . $name;
            $newCase = Cases::create([
                'process_id' => '879e001c-59d5-4afb-958c-15ec7ff269d1',
                'number' => $number,
                'name' => $name,
                'creator' => $creator
            ]);
            $vars = PmVars::where('case_id', $case->case_id)->get()->each(function ($row) use ($newCase) {
                $row->process_id = '879e001c-59d5-4afb-958c-15ec7ff269d1';
                $row->case_id = $newCase->id;
            })->toArray();
            foreach ($vars as $var) {
                Variable::create($var);
            }
        }
        // $cases = Variable::where('key', 'case_number')->get();
        // foreach($cases as $case){
        //     $newCase = Cases::create([
        //         'process_id' => $case->process_id,
        //         'number' => $case->value ? $case->value : 1,
        //         'name' => '',
        //         'creator' => 1
        //     ]);
        //     Variable::where('case_id', $case->case_id)->update(['case_id' => $newCase->id]);
        // }
    });
});
