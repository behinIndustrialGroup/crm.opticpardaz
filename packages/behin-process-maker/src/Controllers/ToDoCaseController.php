<?php 

namespace BehinProcessMaker\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mkhodroo\PMReport\Controllers\TableController;

class ToDoCaseController extends Controller
{
    private $accessToken;

    public function __construct() {
        $this->accessToken = AuthController::getAccessToken();
    }
    function getMyCase()
    {        
        $inbox =  CurlRequestController::send(
            $this->accessToken,
            "/api/1.0/workflow/home/todo"
        );
        return $inbox;

    }

    function form()
    {
        return view('PMViews::todo');
    }
}