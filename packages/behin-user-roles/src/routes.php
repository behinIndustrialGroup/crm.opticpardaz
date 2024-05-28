<?php

use BehinInit\App\Http\Middleware\Access;
use Illuminate\Support\Facades\Route;
use BehinUserRoles\Controllers\GetMethodsController;
use BehinUserRoles\Controllers\GetRoleController;

Route::name('role.')->prefix('role')->middleware(['web', 'auth',Access::class])->group(function(){
    Route::get('list-form', [GetRoleController::class, 'listForm'])->name('listForm');
    Route::get('list', [GetRoleController::class, 'list'])->name('list');
    Route::post('get', [GetRoleController::class, 'get'])->name('get');
    Route::post('edit', [GetRoleController::class, 'edit'])->name('edit');
    Route::post('change-user-role', [GetRoleController::class, 'changeUserRole'])->name('changeUserRole');
});

Route::name('method.')->prefix('method')->middleware(['web', 'auth',Access::class])->group(function(){
    Route::get('list', [GetMethodsController::class, 'list'])->name('list');
    Route::post('edit', [GetMethodsController::class, 'edit'])->name('edit');
});
