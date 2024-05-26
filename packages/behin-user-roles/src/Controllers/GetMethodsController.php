<?php

namespace BehinUserRoles\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use BehinInitApp\Models\Access;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use BehinUserRoles\Models\Method;

class GetMethodsController extends Controller
{
    public static function getByRoleAccess($role_id) {
        return Method::orderBy('category', 'desc')->get()->each(function($row) use($role_id){
            $row->access = Access::where('role_id', $role_id)->where('method_id', $row->id)->first()?->access;
        });
    }

    public static function getAll() {
        return Method::get();
    }

    function list() {
        return view('URPackageView::methods.list')->with([
            'methods' => $this->getAll()
        ]);
    }

    function edit(Request $r) {
        foreach(GetMethodsController::getAll() as $method){
            Method::where('id', $method->id)->update(['category' => $r->input("$method->id")]);
        }
        return response('ok');
    }

}
