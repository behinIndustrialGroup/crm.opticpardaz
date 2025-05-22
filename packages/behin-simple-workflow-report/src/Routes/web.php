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
use Behin\SimpleWorkflowReport\Controllers\Core\RoleReportFormController;
use Behin\SimpleWorkflowReport\Controllers\Core\SummaryReportController;
use BehinProcessMaker\Controllers\VariableController;
use BehinProcessMaker\Models\PMVariable;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Support\Facades\Route;

Route::name('simpleWorkflowReport.')->prefix('workflow-report')->middleware(['web', 'auth'])->group(function () {
    Route::get('index', [ReportController::class, 'index'])->name('index');
    Route::resource('report', ReportController::class);
    Route::resource('summary-report', SummaryReportController::class);
    Route::resource('role', RoleReportFormController::class);
    Route::resource('fin-report', FinReportController::class);
    Route::get('total-payment', [FinReportController::class, 'totalPayment'])->name('totalPayment');
    Route::get('test', function () {
        $images = Variable::where('key', 'device_plaque_image')->whereNotNull('value')->get();
        foreach($images as $image){
            echo $image->case->number . "<a href='". url("public/$image->value")."' download>Download</a><br>";
        }
    });
});
