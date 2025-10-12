<?php

use Behin\SimpleWorkflowReport\Controllers\Scripts\OPPAReportController;
use Behin\SimpleWorkflow\Models\Core\Cases;
use Behin\SimpleWorkflow\Models\Core\Variable;
use Behin\SimpleWorkflowReport\Controllers\Core\AllRequestsReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\CustomersReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\FinReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\ReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\RoleReportFormController;
use Behin\SimpleWorkflowReport\Controllers\Core\SummaryReportController;
use Behin\SimpleWorkflowReport\Controllers\Core\TransActionController;
use Behin\SimpleWorkflowReport\Controllers\Core\RepairIncomeReportController;
use Behin\SimpleWorkflowReport\Controllers\Scripts\PersonelActivityController;
use BehinInit\App\Http\Middleware\Access;
use Illuminate\Support\Facades\Route;

Route::name('simpleWorkflowReport.')->prefix('workflow-report')->middleware(['web', 'auth'])->group(function () {
    Route::get('index', [ReportController::class, 'index'])->name('index');
    Route::resource('report', ReportController::class);
    Route::resource('summary-report', SummaryReportController::class);
    Route::resource('role', RoleReportFormController::class);
    Route::resource('fin-report', FinReportController::class);
    Route::get('customers/export', [CustomersReportController::class, 'export'])->name('customers.export');
    Route::resource('customers', CustomersReportController::class)->except(['create', 'show', 'edit']);
    Route::get('total-payment', [FinReportController::class, 'totalPayment'])->name('totalPayment');
    Route::get('test', function () {
        $images = Variable::where('key', 'device_plaque_image')->whereNotNull('value')->get();
        foreach($images as $image){
            $case = Cases::find($image->case_id);
            echo $case->getVariable('device_name') . " | " . $case->number . "<a href='". url("public/$image->value")."' download='". $case->number .".jpg'>Download</a><br>";
        }
    });

    
    Route::get('all-requests/export', [AllRequestsReportController::class, 'export'])->middleware(Access::class. ':گزارش کل درخواست های ثبت شده')->name('all-requests.export');
    Route::get('all-requests/{case_number}', [AllRequestsReportController::class, 'show'])->middleware(Access::class. ':گزارش کل درخواست های ثبت شده')->name('all-requests.show');
    Route::get('all-requests', [AllRequestsReportController::class, 'index'])->middleware(Access::class. ':گزارش کل درخواست های ثبت شده')->name('all-requests.index');


    Route::resource('oppa-report', OPPAReportController::class);
    Route::resource('transaction-report', TransActionController::class);
    Route::resource('repair-income-report', RepairIncomeReportController::class);

});
