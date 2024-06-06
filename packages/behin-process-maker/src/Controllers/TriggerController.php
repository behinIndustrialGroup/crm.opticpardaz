<?php 

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SoapClient;

class TriggerController extends Controller
{
    public static function list(){
        $sessionId = AuthController::wsdl_login()->message;
        $client = new SoapClient(str_replace('https', 'http', env('PM_SERVER')). '/sysworkflow/en/green/services/wsdl2');
        
        $params = array(array('sessionId'=>$sessionId));
        $params = array(
            array(
                'sessionId'=>$sessionId
            )
        );
        $result = $client->__SoapCall('triggerList', $params);
        return $result->triggers;
    }
    
    public static function excute($triggerId, $caseId, $accessToken = null) {
        if(!$accessToken){
            $accessToken = AuthController::getAccessToken();
        }
        $result =  CurlRequestController::put(
            $accessToken, 
            "/api/1.0/workflow/cases/$caseId/execute-trigger/$triggerId"
        );

        // Log::info("trigger excute result: $result");
        return $result;
    }

    public static function getAvalableTrrigersBeforeTask($processId, $taskId, $caseId){
        $accessToken = AuthController::getAccessToken();
        return CurlRequestController::send(
            $accessToken, 
            "/api/1.0/workflow/project/$processId/activity/$taskId/step/$caseId/available-triggers/before"
        );
    }
}