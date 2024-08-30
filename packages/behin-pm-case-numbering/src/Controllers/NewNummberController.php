<?php

namespace Behin\PMCaseNumbering\Controllers;

use App\Http\Controllers\Controller;
use Behin\PMCaseNumbering\Models\PMCaseNumbering;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewNummberController extends Controller
{
    public static function getNewNumber(Request $request)
    {
        // CHECK API KEY IS VALID 
        $c = ApiKeyController::checkApiKey($request->pro_id, $request->api_key);
        if($c){
            return response(trans(""), 403);
        }

        // CREATE A ROW IF THERE IS NO PRO_ID RECORD
        $number = self::getOrCreate($request->pro_id);

        $number->count = $number->count +1;
        $number->save();
        return $number->count;
    }

    public static function getOrCreate($pro_id){
        $row = PMCaseNumbering::where('process_id', $pro_id)->first();
        if($row){
            return $row;
        }
        return PMCaseNumbering::create([
            'process_id' => $pro_id,
            'api_key' => Str::random(32)
        ]);
    }

}
