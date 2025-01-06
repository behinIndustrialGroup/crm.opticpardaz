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
use Behin\SimpleWorkflowReport\Controllers\Core\ReportController;
use BehinProcessMaker\Models\PMVariable;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Support\Facades\Route;

Route::name('simpleWorkflowReport.')->prefix('workflow-report')->middleware(['web', 'auth'])->group(function () {
    Route::get('index', [ReportController::class, 'index'])->name('index');
    Route::resource('report', ReportController::class);
    Route::get('import', function () {
        // $cases = PmVars::where('key', 'case_number')->get();
        // foreach($cases as $case){
        //     $newCase = Cases::create([
        //         'process_id' => $case->process_id,
        //         'number' => $case->value ? $case->value : 1,
        //         'name' => '',
        //         'creator' => 1
        //     ]);
        //     Variable::create(PmVars::where('case_id', $case->case_id)->get()->each(function($row) use($newCase){$row->case_id = $newCase->id; return $row;})->toArray());
        // }
        $cases = Variable::where('key', 'case_number')->get();
        foreach($cases as $case){
            $newCase = Cases::create([
                'process_id' => $case->process_id,
                'number' => $case->value ? $case->value : 1,
                'name' => '',
                'creator' => 1
            ]);
            Variable::where('case_id', $case->case_id)->update(['case_id' => $newCase->id]);
        }
    });
});
