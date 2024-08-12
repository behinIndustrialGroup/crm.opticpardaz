<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\AuthController;
use BehinProcessMaker\Controllers\CaseController;
use BehinProcessMaker\Controllers\CurlRequestController;
use BehinProcessMaker\Controllers\GetCaseVarsController;
use BehinProcessMaker\Models\PmVars;
use Illuminate\Http\Request;

class CaseInfoController extends Controller
{
    public static function getLightCaseInfo($caseId){
        $accessToken = AuthController::getAccessToken();
        $info = CurlRequestController::send(
            $accessToken, 
            "/api/1.0/workflow/light/participated/case/$caseId"
        );
        return $info;
    }

    public static function get($caseId){
        $info = self::getLightCaseInfo($caseId);
        $delIndex = $info->case->delIndex;
        return CaseController::getCaseInfo($caseId, $delIndex);
    }
}