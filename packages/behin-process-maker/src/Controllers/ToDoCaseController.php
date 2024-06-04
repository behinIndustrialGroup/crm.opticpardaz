<?php 

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mkhodroo\PMReport\Controllers\TableController;

class ToDoCaseController extends Controller
{
    private $accessToken;
    function getMyCase()
    {
        $this->accessToken = AuthController::getAccessToken();
        
        $inbox =  CurlRequestController::send(
            $this->accessToken,
            "/api/1.0/workflow/home/todo"
        );
        // $r = new Request([
        //     'table_name' => 'application'
        // ]);
        // $application = TableController::getData($r)['results'];
        // $application = collect($application);
        // foreach($inbox->data as $data){
        //     $data->APP_DATA = unserialize($application->where('APP_UID', $data->APP_UID)->first()->APP_DATA);
        //     if (isset($data->APP_DATA['MAIN_INFO'])) {
        //         $data->MAIN_INFO = $data->APP_DATA['MAIN_INFO'];
        //     } else {
        //         $data->MAIN_INFO = '';
        //     }
        // }
        return $inbox;

    }

    function form()
    {
        return view('PMViews::todo');
    }
}