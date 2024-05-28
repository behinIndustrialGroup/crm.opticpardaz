<?php 

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SoapClient;

class GetCaseVarsController extends Controller
{
    private $accessToken;

    public function __construct() {
    }
    function getByCaseId($caseId) {
        $this->accessToken = AuthController::getAccessToken();
        return CurlRequestController::send(
            $this->accessToken, 
            "/api/1.0/workflow/cases/$caseId/variables"
        );
    }

    function getMainInfoByCaseId($caseId) {
        $this->accessToken = AuthController::getAccessToken();
        $result =  CurlRequestController::send(
            $this->accessToken, 
            "/api/1.0/workflow/cases/$caseId/variables"
        );

        return isset($result->MAIN_INFO) ? $result->MAIN_INFO : "";
    }
}

class variableStruct {
    public $name;
  }