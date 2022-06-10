<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PlayDetailsResource;
use App\Http\Resources\PlayMasterResource;
use App\Http\Resources\PrintSingleGameInputResource;
use App\Http\Resources\PrintTripleGameInputResource;
use App\Models\GameAllocation;
use App\Models\GameType;
use App\Models\PayOutSlab;
use App\Models\PlayDetails;
use App\Models\PlayMaster;
use App\Models\SingleNumber;
use App\Models\User;
use App\Models\UserRelationWithOther;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlayController extends Controller
{
    public function save_play_details(Request $request)
    {
        $requestedData = $request->json()->all();

//        return response()->json(['inputPlayDetails' => $requestedData['playDetails']], 200);

//        $validator = Validator::make($requestedData,[
//            'playMaster' => 'required',
//            'playDetails' => 'required'
//        ]);
//
//        if($validator->fails()){
//            return response()->json(['position'=>1,'success'=>0,'data'=>null,'error'=>$validator->messages()], 406,[],JSON_NUMERIC_CHECK);
//        }

        $inputPlayMaster = (object)$requestedData['playMaster'];
        $inputPlayDetails = $requestedData['playDetails'];
        $gameAllocation = GameAllocation::whereUserId($inputPlayMaster->terminalId)->first();
        $gameName = ('game'.$inputPlayMaster->gameId);

        if($gameAllocation->$gameName == 0){
            return response()->json(['success'=>0,'data'=>null, 'message' => 'Game not Allocated'], 406,[],JSON_NUMERIC_CHECK);
        }

        //        Validation for PlayMaster
        $rules = array(
            'drawMasterId'=>'required|exists:draw_masters,id',
            'terminalId'=> ['required',
                function($attribute, $value, $fail){
                    $terminal=User::where('id', $value)->where('user_type_id','=',5)->first();
                    if(!$terminal){
                        return $fail($value.' is not a valid terminal id');
                    }
                }],
        );
        $messages = array(
            'drawMasterId.required'=>'Draw time is required',
            'terminalId.required'=>'Terminal Id is required',
        );

        $validator = Validator::make($requestedData['playMaster'],$rules,$messages );

        if ($validator->fails()) {
            return response()->json(['position'=>2,'success'=>0,'data'=>null,'error'=>$validator->messages()], 406,[],JSON_NUMERIC_CHECK);
        }
        //        Validation for PlayMaster complete


        //validation for playDetails
        $rules = array(
            "*.gameTypeId"=>'required|exists:game_types,id',
            '*.singleNumberId' => 'required_if:*.gameTypeId,==,1',
            '*.numberCombinationId' => 'required_if:*.gameTypeId,==,2',
            '*.quantity' => 'bail|required|integer|min:1',
//            '*.mrp' => 'bail|required|integer|min:1'
        );
        $validator = Validator::make($requestedData['playDetails'],$rules );
        if ($validator->fails()) {
            return response()->json(['position'=>3,'success'=>0,'data'=>null,'error'=>$validator->messages()], 406,[],JSON_NUMERIC_CHECK);
        }
        //end of validation for playDetails

        $userRelationId = UserRelationWithOther::whereTerminalId($inputPlayMaster->terminalId)->whereActive(1)->first();
        $payoutSlabValue = (PayOutSlab::find((User::find($inputPlayMaster->terminalId))->pay_out_slab_id))->slab_value;
        $user = User::find($inputPlayMaster->terminalId);

        $output_array = array();

        DB::beginTransaction();
        try{

            $playMaster = new PlayMaster();
            $playMaster->draw_master_id = $inputPlayMaster->drawMasterId;
            $playMaster->user_id = $inputPlayMaster->terminalId;
            $playMaster->game_id = $inputPlayMaster->gameId;
//            $playMaster->user_relation_id = $inputPlayMaster->userRelationId;
            $playMaster->user_relation_id = $userRelationId->id;
            $playMaster->save();
            $output_array['play_master'] = new PlayMasterResource($playMaster);

            $output_play_details = array();
            foreach($inputPlayDetails as $inputPlayDetail){
                $detail = (object)$inputPlayDetail;
                $gameType = GameType::find($detail->gameTypeId);
                //insert value for triple
                if($detail->gameTypeId == 2){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->numberCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->terminal_payout = $payoutSlabValue;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }
                if($detail->gameTypeId == 1){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

//                    $numberCombinationIds = SingleNumber::find($detail->singleNumberId)->number_combinations->pluck('id');
//                    foreach ($numberCombinationIds as $numberCombinationId){
                        $playDetails = new PlayDetails();
                        $playDetails->play_master_id = $playMaster->id;
                        $playDetails->game_type_id = $detail->gameTypeId;
//                        $playDetails->combination_number_id = $numberCombinationId;
                        $playDetails->combination_number_id = $detail->singleNumberId;
                        $playDetails->quantity = $detail->quantity;
//                        $playDetails->mrp = round($detail->mrp/22,4);
                        $playDetails->mrp = $gameType->mrp;
                        $playDetails->commission = $user->commission;
                        $playDetails->global_payout = $gameType->payout;
                        $playDetails->terminal_payout = $payoutSlabValue;
//                        $playDetails->multiplexer = $gameType->multiplexer;
                        $playDetails->save();
                        $output_play_details[] = $playDetails;

//                    }
                }
                if(($detail->gameTypeId == 3) or ($detail->gameTypeId == 4)){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->cardCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

                if($detail->gameTypeId == 5){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->doubleCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

                if($detail->gameTypeId == 6){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->singleNumberId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

                if($detail->gameTypeId == 7){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->doubleCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

                if($detail->gameTypeId == 8){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->amdarCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

                if($detail->gameTypeId == 9){

                    if($detail->numberCombinationId == 0){
                        continue;
                    }

                    $playDetails = new PlayDetails();
                    $playDetails->play_master_id = $playMaster->id;
                    $playDetails->game_type_id = $detail->gameTypeId;
                    $playDetails->combination_number_id = $detail->baharCombinationId;
                    $playDetails->quantity = $detail->quantity;
                    $playDetails->mrp = $gameType->mrp;
                    $playDetails->commission = $user->commission;
                    $playDetails->global_payout = $gameType->payout;
                    $playDetails->terminal_payout = $payoutSlabValue;
//                    $playDetails->multiplexer = $gameType->multiplexer;
                    $playDetails->save();
                    $output_play_details[] = $playDetails;
                }

            }
//            $output_array['game_input'] = $this->get_game_input_details_by_play_master_id($playMaster->id);

            $amount = $playMaster->play_details->sum(function($t){
                return $t->quantity * $t->mrp;
            });

            $userClosingBalance = (User::find($inputPlayMaster->terminalId))->closing_balance;

            if($userClosingBalance < $amount){
                DB::rollBack();
                return response()->json(['success'=>0,'data'=> null, 'message' => 'Low balance'], 200,[],JSON_NUMERIC_CHECK);
            }

            $output_array['amount'] = round($amount,0);

            $terminal = User::findOrFail($inputPlayMaster->terminalId);
            $terminal->closing_balance-= $amount;
            $terminal->save();

            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0,'exception'=>$e->getMessage(),'error_line'=>$e->getLine(),'file_name' => $e->getFile()], 500);
        }

        return response()->json(['success'=>1,'data'=> $output_array], 200,[],JSON_NUMERIC_CHECK);
    }

    public function get_game_input_details_by_play_master_id($play_master_id){
        $output_array = array();
        $single_game_data = PlayDetails::select(DB::raw('max(single_numbers.single_number) as single_number')
            ,DB::raw('max(play_details.quantity) as quantity'))
            ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
            ->join('single_numbers','number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',1)
            ->groupBy('single_numbers.id')
            ->orderBy('single_numbers.single_order')
            ->get();
        $output_array['single_game_data'] = PrintSingleGameInputResource::collection($single_game_data);

        $triple_game_data = PlayDetails::select('number_combinations.visible_triple_number','play_details.quantity',
            'single_numbers.single_number')
            ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
            ->join('single_numbers','number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',2)
            ->orderBy('single_numbers.single_order')
            ->get();
        $output_array['triple_game_data'] = PrintTripleGameInputResource::collection($triple_game_data);

        return $output_array;
    }
}
