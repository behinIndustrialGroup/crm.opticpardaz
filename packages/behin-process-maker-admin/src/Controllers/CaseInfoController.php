<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\AuthController;
use BehinProcessMaker\Controllers\CurlRequestController;
use BehinProcessMaker\Controllers\GetCaseVarsController;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Http\Request;

class CaseInfoController extends Controller
{
    public static function get($caseId){
        $accessToken = AuthController::getAccessToken();
        return CurlRequestController::send(
            $accessToken, 
            "/api/1.0/workflow/light/participated/case/$caseId"
        );
    }
}