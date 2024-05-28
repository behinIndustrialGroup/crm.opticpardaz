<?php 

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use BehinProcessMaker\Models\PMTask;
use SoapClient;

class TaskController extends Controller
{
    public static function getByTaskId($taskId){
        return PMTask::where('task_uid', $taskId)->first();
    }

    public static function getByProcessId($process_id){
        return PMTask::where('process_uid', $process_id)->get()->toArray();
    }

    public static function saveToDb($pro_uid, $task_uid, $task_title){
        PMTask::updateOrCreate(
            [
                'process_uid' => $pro_uid,
                'task_uid' => $task_uid
            ],
            [
                'task_title' => $task_title
            ]
        );
    }
    
}