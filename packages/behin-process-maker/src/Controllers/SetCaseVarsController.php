<?php

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SoapClient;

class SetCaseVarsController extends Controller
{
    private $accessToken;
    private static $system_vars;

    public function __construct()
    {
    }
    function saveAndNext(Request $r)
    {
        $this->save($r);

        // $system_vars = (new GetCaseVarsController())->getByCaseId($r->caseId);
        $steps = StepController::list(self::$system_vars->PROCESS, self::$system_vars->TASK);
        foreach ($steps as $step) {
            $triggers = $step->triggers;
            foreach ($triggers as $trigger) {
                if ($trigger->st_type === "AFTER") {
                    $result = TriggerController::excute($trigger->tri_uid, self::$system_vars->APPLICATION);
                    if($result?->original){
                        Log::info("Trigger Executed");
                        $result = iconv("UTF-8", "ISO-8859-1", $result->original);
                        return response(str_replace("Bad Request: ", "", $result), 400);
                    }
                }
            }
        }

        return RouteCaseController::next($r->caseId, $r->del_index);
        return response("انجام شد", 200);
    }

    function save(Request $r)
    {
        self::$system_vars = (new GetCaseVarsController())->getByCaseId($r->caseId);
        $sessionId = AuthController::wsdl_login()->message;
        $client = new SoapClient(str_replace('https', 'http', env('PM_SERVER')) . '/sysworkflow/en/green/services/wsdl2');
        $vars = $r->except(
            'caseId',
            'SYS_LANG',
            'SYS_SKIN',
            'SYS_SYS',
            'APPLICATION',
            'PROCESS',
            'TASK',
            'INDEX',
            'USER_LOGGED',
            'USR_USERNAME',
            'APP_NUMBER',
            'PIN'
        );
        $variables = array();
        $local_fields = GetCaseVarsController::getVarsFromLocal(self::$system_vars->PROCESS, $r->caseId);
        foreach ($vars as $key => $val) {
            if (gettype($val) == 'object') {
                $field_name = explode("-", $key)[0];
                $fileId = explode("-", $key)[1];
                InputDocController::upload($r->file($key), $r->taskId, $r->caseId, $fileId, self::$system_vars->USER_LOGGED, $field_name );
            }elseif(gettype($val) == 'array'){
                foreach($val as $pic){
                    SaveVarsController::saveDoc(self::$system_vars->PROCESS, $r->caseId, $key, $pic);
                }
            } else {
                $obj = new variableListStruct();
                $obj->name = $key;
                $obj->value = $val;
                $variables[] = $obj;
                SaveVarsController::save(self::$system_vars->PROCESS, $r->caseId, $key, $val);
            }
        }
        $params = array(array('sessionId' => $sessionId, 'caseId' => $r->caseId, 'variables' => $variables));
        $result = $client->__SoapCall('sendVariables', $params);
        if ($result->status_code != 0)
            return response($result->message, 400);


        return response("ok", 200);
    }
}

class variableListStruct
{
    public $name;
    public $value;
}
