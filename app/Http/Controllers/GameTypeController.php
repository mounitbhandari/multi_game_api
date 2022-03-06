<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameTypeResource;
use App\Models\Game;
use App\Models\GameType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameTypeController extends Controller
{
    public function index()
    {
        $result = GameType::get();
//        $result = get_age('1977-05-20');
        // return response()->json(['success'=>1,'data'=> $result], 200,[],JSON_NUMERIC_CHECK);
        return response()->json(['success'=>1,'data'=> GameTypeResource::collection($result)], 200,[],JSON_NUMERIC_CHECK);
    }


    public function update_payout(Request $request){
        $requestedData = $request->json()->all();
        $inputPayoutDetails = $requestedData;

//        $idGameType = [1,2,5];

//        return response()->json(['success'=>$inputPayoutDetails[0], 'data' => ($inputPayoutDetails[0])['gameTypeId']], 200);

        if(($inputPayoutDetails[0])['gameTypeId'] == 1){
            $idGameType = [1,2,5];
            for($i=0; $i<3; $i++){
                $gameType = GameType::find($idGameType[$i]);
                $gameType->payout = ($inputPayoutDetails[0])['newPayout'];
                $gameType->save();
            }
            $getAllGameType = GameType::get();
            return response()->json(['success'=>1,'data'=> GameTypeResource::collection($getAllGameType)], 200,[],JSON_NUMERIC_CHECK);
        }

        DB::beginTransaction();
        try{
            $outputPayoutDetails = array();
            foreach($inputPayoutDetails as $inputPayoutDetail){
                $detail = (object)$inputPayoutDetail;
                $gameType = GameType::find($detail->gameTypeId);
                $gameType->payout = $detail->newPayout;
                $gameType->save();
                $outputPayoutDetails[] = $gameType;
            }
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }

        $getAllGameType = GameType::get();


        return response()->json(['success'=>1,'data'=> GameTypeResource::collection($getAllGameType)], 200,[],JSON_NUMERIC_CHECK);
    }


}
