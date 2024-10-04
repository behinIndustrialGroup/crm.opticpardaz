<?php

namespace BehinProcessMakerAdmin;

use BehinProcessMaker\Controllers\GetTaskAsigneeController;
use BehinProcessMakerAdmin\Controllers\AllCasesController;
use BehinProcessMakerAdmin\Controllers\CaseDetailsController;
use BehinProcessMakerAdmin\Controllers\CaseHistoryController;
use BehinProcessMakerAdmin\Controllers\CaseInfoController;
use BehinProcessMakerAdmin\Controllers\CasesByLastStatusController;
use BehinProcessMakerAdmin\Controllers\DeleteCaseController;
use BehinProcessMakerAdmin\Controllers\ReassignCaseController;
use Illuminate\Support\Facades\Route;

Route::name('pmAdmin.')->prefix('pm-admin')->middleware(['web', 'auth'])->group(function(){
    Route::name('form.')->prefix('form')->group(function(){
        Route::get('all-cases', [AllCasesController::class, 'allCasesForm'])->name('allCasesForm');
        Route::post('case-details', [CaseDetailsController::class, 'caseDetails'])->name('caseDetails');
        Route::post('case-history', [CaseHistoryController::class, 'caseHistoryForm'])->name('caseHistoryForm');

        Route::get('cases-by-last-status', [CasesByLastStatusController::class, 'casesByLastStatusView'])->name('casesByLastStatusView');

    });
    Route::name('api.')->prefix('api')->group(function(){
        Route::get('all-cases', [AllCasesController::class, 'all'])->name('all');
        Route::get('task-assignee', [GetTaskAsigneeController::class, 'getAssignees']);
        Route::post('delete-case', [DeleteCaseController::class, 'delete'])->name('deleteCase');
        Route::post('reassign-case', [ReassignCaseController::class, 'reassign'])->name('reassign');

        Route::post('cases-by-last-status', [CasesByLastStatusController::class, 'casesByLastStatus'])->name('casesByLastStatus');

    });

});
