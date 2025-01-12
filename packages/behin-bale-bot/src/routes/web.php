<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use BaleBot\Controllers\BotController;

Route::name('baleBot.')->prefix('bale-bot')->group(function(){
    Route::post('/chat', [BotController::class, 'chat'])->name('chat');
});
