<?php

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use BehinProcessMaker\Models\PMUsers;
use BehinProcessMaker\Models\PMVacation;

class PMUserController extends Controller
{
    public static function getByName($user_name) {
        return PMUsers::where('USR_USERNAME', $user_name)->first();
    }
}
