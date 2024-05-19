<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


Route::get('migrate', function () {
    Artisan::call('migrate');
});
