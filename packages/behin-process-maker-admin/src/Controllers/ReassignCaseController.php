<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\AuthController;
use BehinProcessMaker\Controllers\CaseController;
use BehinProcessMaker\Controllers\CaseTrackerController;
use BehinProcessMaker\Controllers\CurlRequestController;
use BehinProcessMaker\Controllers\GetCaseVarsController;
use BehinProcessMaker\Controllers\GetTaskAsigneeController;
use BehinProcessMaker\Controllers\ReassignCaseController as ControllersReassignCaseController;
use BehinProcessMaker\Controllers\TaskController;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReassignCaseController extends Controller
{
    public static function reassign(Request $r){
        return ControllersReassignCaseController::reassign($r->caseId, '884962377668917c92d7603066296900', '00000000000000000000000000000001');
    }

}