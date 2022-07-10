<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrawMasterResource;
use App\Models\DrawMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Test extends Controller
{
    public function index()
    {
        $result = DrawMaster::whereDoesnthave('result_masters', function($q){
            $q->where('game_date', '=', '2021-05-24');
        })->get();
        return response()->json(['success'=>1,'data'=>$result], 200,[],JSON_NUMERIC_CHECK);
    }

    public function testNew(){
        $x= [40,30,20,10];
        $gg = $x[array_rand($x)];
        return response()->json(['success'=>$gg, 'test1' => $ff], 200,[],JSON_NUMERIC_CHECK);
    }

}
