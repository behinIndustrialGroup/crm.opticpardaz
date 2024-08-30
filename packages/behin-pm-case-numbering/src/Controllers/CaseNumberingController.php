<?php

namespace Behin\PMCaseNumbering\Controllers;

use App\Http\Controllers\Controller;
use Behin\PMCaseNumbering\Models\PMCaseNumbering;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaseNumberingController extends Controller
{
    public static function getAll()
    {
        return PMCaseNumbering::get();
    }

    public static function form(){
        return view('CaseNumberingView::index')->with([
            'rows' => self::getAll()
        ]);
    }

}
