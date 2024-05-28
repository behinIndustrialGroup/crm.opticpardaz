<?php 

namespace BehinInit\app\Http\Controllers;

use App\Http\Controllers\Controller;
use BehinInit\app\Models\Access;
use Illuminate\Support\Facades\Auth;
use BehinUserRoles\Models\Method;

class TestController extends Controller
{

    function create() {
        return "test";
    }
}