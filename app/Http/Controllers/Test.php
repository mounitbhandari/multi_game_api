<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrawMasterResource;
use App\Models\DrawMaster;
use App\Models\PlayMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Litespeed\LSCache\LSCache;

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
        $set_game_date = Carbon::today()->addDays(1)->format('Y-m-d');
        return $set_game_date;

//        $today= Carbon::today()->format('Y-m-d');
//        $nPlay = PlayMaster::whereDrawMasterId(6)
//            ->whereDate('created_at',$today)
//            ->get();
//        return response()->json(['success'=>1, 'test1' => $nPlay], 200,[],JSON_NUMERIC_CHECK);

        //clear cache
//        LSCache::purgeAll();

        //get referer
        return request()->headers->get('referer');
//        $current_time = Carbon::now();

        //get server ip address
//        $localIp = gethostbyname(gethostname());
//        return request()->server('SERVER_ADDR');
    }

}
