<?php

namespace BehinProcessMakerAdmin\Controllers;

use App\Http\Controllers\Controller;
use BehinProcessMaker\Controllers\PMUserController;
use BehinProcessMaker\Models\PmVars;

class CaseFinListController extends Controller
{
    public function finListView(){
        return view('PMAdminViews::fin-report.list')->with([
            'last_statuses' => CaseLastStatusController::getAllStatus()
        ]);
    }


}
