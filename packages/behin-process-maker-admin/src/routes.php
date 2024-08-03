<?php

namespace BehinProcessMakerAdmin;

use BehinProcessMakerAdmin\Controllers\AllCasesController;
use BehinProcessMakerAdmin\Controllers\CaseDetailsController;
use Illuminate\Support\Facades\Route;

Route::name('pmAdmin.')->prefix('pm-admin')->middleware(['web', 'auth'])->group(function(){
    Route::name('form.')->prefix('form')->group(function(){
        Route::get('all-cases', [AllCasesController::class, 'form']);
        Route::post('case-details', [CaseDetailsController::class, 'caseDetails'])->name('caseDetails');
    });
    Route::name('api.')->prefix('api')->group(function(){
        Route::get('all-cases', [AllCasesController::class, 'all'])->name('all');

    });

});
