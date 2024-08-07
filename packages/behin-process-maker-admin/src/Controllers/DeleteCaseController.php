<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\AuthController;
use BehinProcessMaker\Controllers\CurlRequestController;
use BehinProcessMaker\Controllers\GetCaseVarsController;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Http\Request;

class DeleteCaseController extends Controller
{
    public static function delete(Request $r){
        $caseRows = GetCaseController::getCaseRowsFromLocalDb($r->processId, $r->caseId);
        $caseRows->each(function($row){
            $row->delete();
        });
        return response("");
    }
}