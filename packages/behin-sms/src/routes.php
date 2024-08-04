<?php

use Behin\Sms\Controllers\SendSmsController;
use Illuminate\Support\Facades\Route;
use Mkhodroo\SmsTemplate\Controllers\SmsTemplateController;
use Mkhodroo\Voip\Controllers\VoipController;

Route::name('sms.')->prefix('sms')->group(function(){
    Route::get('{sms_id}/{to}/{params?}', [SmsTemplateController::class, 'send'])->name('send');
    Route::post('send', [SendSmsController::class, 'send'])->name('send');
});