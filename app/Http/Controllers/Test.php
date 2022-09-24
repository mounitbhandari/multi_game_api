<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DrawMasterResource;
use App\Models\DrawMaster;
use App\Models\Game;
use App\Models\NumberCombination;
use App\Models\PlayDetails;
use App\Models\PlayMaster;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Litespeed\LSCache\LSCache;
use Litespeed\LSCache\LSCacheMiddleware;
use PhpParser\Node\Expr\Cast\Object_;
use Psy\Util\Json;

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

//        $transaction = new Transaction();
//        $transaction->terminal_id = 12;
//        $transaction->play_master_id = 4;
//        $transaction->old_amount = 200;
//        $transaction->prize_amount = 10;
//        $transaction->new_amount = 500;
//        $transaction->save();

//        $transaction = DB::insert("insert into transactions (
//              terminal_id
//              ,play_master_id
//              ,old_amount
//              ,prize_amount
//              ,new_amount
//            ) VALUES (
//              ? -- terminal_id - IN bigint unsigned
//              ,? -- play_master_id - IN int
//              ,? -- old_amount - IN decimal(50,2)
//              ,? -- prize_amount - IN decimal(50,2)
//              ,? -- new_amount - IN decimal(50,2)
//            )", array(12,4,200,10,500));
//
//        return response()->json(['success'=>1,'data'=>$transaction], 200,[],JSON_NUMERIC_CHECK);

//        $test = DB::select("select * from play_masters
//            where date(created_at) = '2022-09-14'
//            order by id desc
//            limit 1")[0];
//
//        $date = Carbon::parse($test->created_at)->format('Y-m-d');
//        $datework = Carbon::createFromDate($date);
//        $now = Carbon::now();
//        $testdate = $datework->diffInDays($now);
//
//        return $testdate;

//        Cache::get('allTerminal');

        $value = Cache::remember('users', 100, function () {
            return Game::get();
    });

//        $newa = DB::select("select game_type_id from ?",[collect($value)->all()]);


        return collect($value)->where('game_type_id', 1)->all();
//        return Object.entries(obj) collect($value)->where('game_type_id', 1)->all();
//        return json_decode(json_encode(collect($value)->where('game_type_id', 1)->all()), true)->to;

//        return $newa;

//        $set_game_date = Carbon::today()->addDays(1)->format('Y-m-d');
//         if((Carbon::today()->format('Y-m-d')) === Carbon::today()->addDays(1)->format('Y-m-d')){
//             $test = true;
//        }else{
//             $test = false;
//         }
//        return $test;

//        $today= Carbon::today()->format('Y-m-d');
//        $nPlay = PlayMaster::whereDrawMasterId(6)
//            ->whereDate('created_at',$today)
//            ->get();
//        return response()->json(['success'=>1, 'test1' => $nPlay], 200,[],JSON_NUMERIC_CHECK);

        //clear cache
//        LSCache::purgeAll();

        //get referer
//        return request()->headers->get('referer');
//        $current_time = Carbon::now();

        //get server ip address
//        $localIp = gethostbyname(gethostname());
//        return request()->server('SERVER_ADDR');
    }

}
