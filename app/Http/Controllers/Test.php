<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrawMasterResource;
use App\Models\DrawMaster;
use App\Models\PlayMaster;
use Carbon\Carbon;
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
        $today= Carbon::today()->format('Y-m-d');
        $nPlay = PlayMaster::whereDrawMasterId(6)
            ->whereDate('created_at',$today)
            ->get();
        return response()->json(['success'=>1, 'test1' => $nPlay], 200,[],JSON_NUMERIC_CHECK);
    }

}
