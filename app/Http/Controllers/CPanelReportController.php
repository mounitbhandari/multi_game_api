<?php

namespace App\Http\Controllers;

use App\Models\CardCombination;
use App\Models\DoubleNumberCombination;
use App\Models\DrawMaster;
use App\Models\Game;
use App\Models\GameType;
use App\Models\NextGameDraw;
use App\Models\NumberCombination;
use App\Models\ResultDetail;
use App\Models\ResultMaster;
use App\Models\SingleNumber;
use App\Models\Transaction;
use App\Models\User;
use Faker\Core\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Models\PlayMaster;
use App\Models\PlayDetails;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Litespeed\LSCache\LSCache;

class CPanelReportController extends Controller
{
    public function barcode_wise_report(){
        $x = $this->get_total_quantity_by_barcode(1);

        $data = PlayMaster::select('play_masters.id as play_master_id', DB::raw('substr(play_masters.barcode_number, 1, 8) as barcode_number')
            ,'draw_masters.visible_time as draw_time',
            'users.email as terminal_pin','play_masters.created_at as ticket_taken_time'
            )
            ->join('draw_masters','play_masters.draw_master_id','draw_masters.id')
            ->join('users','users.id','play_masters.user_id')
            ->join('play_details','play_details.play_master_id','play_masters.id')
            ->where('play_masters.is_cancelled',0)
            ->groupBy('play_masters.id','play_masters.barcode_number','draw_masters.visible_time','users.email','play_masters.created_at')
            ->orderBy('play_masters.created_at','desc')
            ->get();

        foreach($data as $x){
            $detail = (object)$x;
            $detail->total_quantity = $this->get_total_quantity_by_barcode($detail->play_master_id);
            $detail->prize_value = $this->get_prize_value_by_barcode($detail->play_master_id);
            $detail->amount = $this->get_total_amount_by_barcode($detail->play_master_id);
        }
        return response()->json(['success'=> 1, 'data' => $data], 200);
    }

    public function barcode_wise_report_by_date(Request $request){
        $requestedData = (object)$request->json()->all();

        $start_date = $requestedData->startDate;
        $end_date = $requestedData->endDate;


        $data = PlayMaster::select('play_masters.id as play_master_id', DB::raw('substr(play_masters.barcode_number, 1, 8) as barcode_number')
            ,'draw_masters.visible_time as draw_time','draw_masters.id as draw_master_id','play_masters.created_at','games.id as game_id',
            'users.email as terminal_pin','play_masters.created_at as ticket_taken_time','games.game_name','play_masters.is_claimed', 'games.id as game_id'
        )
            ->join('draw_masters','play_masters.draw_master_id','draw_masters.id')
            ->join('users','users.id','play_masters.user_id')
            ->join('play_details','play_details.play_master_id','play_masters.id')
            ->join('game_types','game_types.id','play_details.game_type_id')
            ->join('games','games.id','game_types.game_id')
            ->where('play_masters.is_cancelled',0)
//            ->where('play_masters.created_at','>=',$start_date)
//            ->where('play_masters.created_at','<=',$end_date)
            ->whereRaw('date(play_masters.created_at) >= ?', [$start_date])
            ->whereRaw('date(play_masters.created_at) <= ?', [$end_date])
            ->groupBy('play_masters.id','play_masters.barcode_number',
                'draw_masters.visible_time','users.email','play_masters.created_at',
                'games.game_name','play_masters.is_claimed', 'games.id','draw_masters.id')
            ->orderBy('play_masters.created_at','desc')
            ->get();

        foreach($data as $x){
            $detail = (object)$x;

//            $detail->result = Cache::remember(((String)$detail->play_master_id).'result', 3000000, function () use ($detail) {
//                $result = ResultMaster::whereDrawMasterId($detail->draw_master_id)->whereGameDate($detail->created_at->format('Y-m-d'))->whereGameId($detail->game_id)->first();
//                if($result){
//                    if($detail->game_id == 1){
//                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(2)->first();
//                        $showNumber = (NumberCombination::find($resultDetails->combination_number_id))->visible_triple_number;
//                    }else if($detail->game_id == 2){
//                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(3)->first();
//                        $x = CardCombination::find($resultDetails->combination_number_id);
//                        $showNumber = $x->rank_name. ' ' .$x->suit_name;
//                    }else if($detail->game_id == 3){
//                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(4)->first();
//                        $x = CardCombination::find($resultDetails->combination_number_id);
//                        $showNumber = $x->rank_name. ' ' .$x->suit_name;
//                    }else if($detail->game_id == 4){
//                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(6)->first();
//                        $showNumber = (SingleNumber::find($resultDetails->combination_number_id))->single_number;
//                    }else if($detail->game_id == 5){
//                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(7)->first();
//                        $showNumber = (DoubleNumberCombination::find($resultDetails->combination_number_id))->visible_double_number;
//                    }
//                }else{
//                    $showNumber = "---";
//                }

//                return $showNumber;
//            });

            if((Cache::has((String)$detail->play_master_id).'result') == 1){
                $detail->result = Cache::remember(((String)$detail->play_master_id).'result', 3000000, function (){
                });
            }else{
                $result = ResultMaster::whereDrawMasterId($detail->draw_master_id)->whereGameDate($detail->created_at->format('Y-m-d'))->whereGameId($detail->game_id)->first();
                if($result){
                    if($detail->game_id == 1){
                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(2)->first();
                        $showNumber = (NumberCombination::find($resultDetails->combination_number_id))->visible_triple_number;
                    }else if($detail->game_id == 2){
                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(3)->first();
                        $x = CardCombination::find($resultDetails->combination_number_id);
                        $showNumber = $x->rank_name. ' ' .$x->suit_name;
                    }else if($detail->game_id == 3){
                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(4)->first();
                        $x = CardCombination::find($resultDetails->combination_number_id);
                        $showNumber = $x->rank_name. ' ' .$x->suit_name;
                    }else if($detail->game_id == 4){
                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(6)->first();
                        $showNumber = (SingleNumber::find($resultDetails->combination_number_id))->single_number;
                    }else if($detail->game_id == 5){
                        $resultDetails = ResultDetail::whereResultMasterId($result->id)->whereGameTypeId(7)->first();
                        $showNumber = (DoubleNumberCombination::find($resultDetails->combination_number_id))->visible_double_number;
                    }
                    $detail->result = Cache::remember(((String)$detail->play_master_id).'result', 3000000, function () use ($showNumber) {
                        return $showNumber;
                    });
                }else{
                    $showNumber = "---";
                    $detail->result = $showNumber;
                }
            }

            $detail->total_quantity = Cache::remember(((String)$detail->play_master_id).'total_quantity', 3000000, function () use ($detail) {
               return  $this->get_total_quantity_by_barcode($detail->play_master_id);
            });

            $detail->prize_value = Cache::remember(((String)$detail->play_master_id).'prize_value', 3000000, function () use ($detail) {
               return $this->get_prize_value_by_barcode($detail->play_master_id);
            });

            $detail->amount = Cache::remember(((String)$detail->play_master_id).'amount', 3000000, function () use ($detail) {
                return $this->get_total_amount_by_barcode($detail->play_master_id);
            });

//            $detail->total_quantity = $this->get_total_quantity_by_barcode($detail->play_master_id);
//            $detail->prize_value = $this->get_prize_value_by_barcode($detail->play_master_id);
//            $detail->amount = $this->get_total_amount_by_barcode($detail->play_master_id);
        }

        return response()->json(['success'=> 1, 'data' => $data], 200,[],JSON_NUMERIC_CHECK);

    }



    public function get_barcode_report_particulars($play_master_id){
        $data = array();
        $playMaster = PlayMaster::findOrFail($play_master_id);
        $data['barcode'] = Str::substr($playMaster->barcode_number,0,8);

        $singleGameData = PlayDetails::select(DB::raw('max(single_numbers.single_number) as single_number')
            ,DB::raw('max(play_details.quantity) as quantity'))
//            ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
            ->join('single_numbers','play_details.combination_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',1)
            ->groupBy('single_numbers.id')
            ->orderBy('single_numbers.single_order')
            ->get();

        $data['single'] = $singleGameData;

        $tripleGameData = PlayDetails::select('number_combinations.visible_triple_number','single_numbers.single_number'
            ,'play_details.quantity')
            ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
            ->join('single_numbers','number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',2)
            ->orderBy('number_combinations.visible_triple_number')
            ->get();
        $data['triple'] = $tripleGameData;

        $doubleGameData = PlayDetails::select('double_number_combinations.visible_double_number','single_numbers.single_number'
            ,'play_details.quantity')
            ->join('double_number_combinations','play_details.combination_number_id','double_number_combinations.id')
            ->join('single_numbers','double_number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',5)
            ->orderBy('double_number_combinations.visible_double_number')
            ->get();
        $data['double'] = $doubleGameData;

        $twelveCard = PlayDetails::select('card_combinations.rank_name','card_combinations.suit_name'
            ,'play_details.quantity')
            ->join('card_combinations','play_details.combination_number_id','card_combinations.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',3)
            ->where('card_combinations.card_combination_type_id',1)
            ->get();
        $data['twelveCard'] = $twelveCard;

        $sixteenCard = PlayDetails::select('card_combinations.rank_name','card_combinations.suit_name'
            ,'play_details.quantity')
            ->join('card_combinations','play_details.combination_number_id','card_combinations.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',4)
            ->where('card_combinations.card_combination_type_id',2)
            ->get();
        $data['sixteenCard'] = $sixteenCard;

        $sixteenCard = PlayDetails::select('card_combinations.rank_name','card_combinations.suit_name'
            ,'play_details.quantity')
            ->join('card_combinations','play_details.combination_number_id','card_combinations.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',4)
            ->where('card_combinations.card_combination_type_id',2)
            ->get();
        $data['sixteenCard'] = $sixteenCard;
//
        $singleGameData = PlayDetails::select(DB::raw('max(single_numbers.single_number) as single_number')
            ,DB::raw('max(play_details.quantity) as quantity'))
            ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
            ->join('single_numbers','number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',6)
            ->groupBy('single_numbers.id')
            ->orderBy('single_numbers.single_order')
            ->get();
        $data['singleIndividual'] = $singleGameData;
//
        $doubleGameData = PlayDetails::select('double_number_combinations.visible_double_number','single_numbers.single_number'
            ,'play_details.quantity')
            ->join('double_number_combinations','play_details.combination_number_id','double_number_combinations.id')
            ->join('single_numbers','double_number_combinations.single_number_id','single_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',7)
            ->orderBy('single_numbers.single_order')
            ->get();
        $data['doubleIndividual'] = $doubleGameData;
//
        $andarNumber = PlayDetails::select('andar_numbers.andar_number'
            ,'play_details.quantity')
            ->join('andar_numbers','play_details.combination_number_id','andar_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',8)
            ->get();
        $data['andarNumber'] = $andarNumber;
//
        $baharNumber = PlayDetails::select('bahar_numbers.bahar_number'
            ,'play_details.quantity')
            ->join('bahar_numbers','play_details.combination_number_id','bahar_numbers.id')
            ->where('play_details.play_master_id',$play_master_id)
            ->where('play_details.game_type_id',9)
            ->get();
        $data['baharNumber'] = $baharNumber;


        return response()->json(['success'=> 1, 'data' => $data], 200);

    }

    public function total_sale_by_play_master_id($id){

        $totalSale = 0;

        $playDetails = PlayDetails::wherePlayMasterId($id)->get();

        foreach ($playDetails as $playDetail){
            $gameType = GameType::find($playDetail->game_type_id);
            $totalSale = $totalSale + ($gameType->mrp * $playDetail->quantity);
        }

        return $totalSale;
    }

    public function draw_wise_report(Request $request){
        $requestedData = (object)$request->json()->all();
        $gameId = $requestedData->game_id;
        $today= Carbon::today()->format('Y-m-d');
        $test = 0;
        $total_prize = 0;
        $total_sale = 0;
        $total_quantity = 0;
        $total_commission = 0;
        $commission_percentage = 0;
        $return_array = [];
        $data = null;

        $draw_times = DB::select("select draw_master_id from play_masters where date(created_at) = ?",[$today]);

        foreach ($draw_times as $draw_time){
            $data = null;
            $total_prize = 0;
            $total_sale = 0;
            $total_quantity = 0;
            $total_commission = 0;
            $commission_percentage = 0;

            if($gameId == 0){
                $data = DB::select("select play_masters.id, play_masters.barcode_number, play_masters.draw_master_id, play_masters.user_id, play_masters.game_id,
                   play_masters.user_relation_id, play_masters.is_claimed, play_masters.is_cancelled, play_masters.is_cancelable, play_masters.created_at, play_masters.updated_at,
                   draw_masters.draw_name, draw_masters.visible_time from play_masters
                   inner join draw_masters ON draw_masters.id = play_masters.draw_master_id
                   where date(play_masters.created_at) = ? and play_masters.draw_master_id =".$draw_time->draw_master_id,[$today]);
            }else{
                $data = DB::select("select play_masters.id, play_masters.barcode_number, play_masters.draw_master_id, play_masters.user_id, play_masters.game_id,
                   play_masters.user_relation_id, play_masters.is_claimed, play_masters.is_cancelled, play_masters.is_cancelable, play_masters.created_at, play_masters.updated_at,
                   draw_masters.draw_name, draw_masters.visible_time from play_masters
                   inner join draw_masters ON draw_masters.id = play_masters.draw_master_id
                   where date(play_masters.created_at) = ? and play_masters.game_id = ".$gameId." and play_masters.draw_master_id =".$draw_time->draw_master_id,[$today]);
            }

            foreach ($data as $x){
                $total_prize = $total_prize + (int)$this->get_prize_value_by_barcode($x->id);
                $total_quantity = $total_quantity + $this->get_total_quantity_by_barcode($x->id);
                $total_sale = $total_sale + $this->total_sale_by_play_master_id($x->id);
                $total_commission = (DB::select("select ((max(commission)/100)*".$total_sale.") as commission from play_details where play_master_id = ".$x->id))[0]->commission;
                $commission_percentage = (DB::select("select (max(commission)/100) as commission from play_details where play_master_id = ".$x->id))[0]->commission;
            }

            $temp_arr = [
                'draw_id' => $draw_time->draw_master_id,
                'draw_time' => DrawMaster::find($draw_time->draw_master_id)->visible_time,
                'total_sale' => $total_sale,
                'total_prize' => $total_prize,
                'total_quantity' =>$total_quantity,
                'total_commission' =>$total_commission,
                'commission_percentage' =>$commission_percentage
            ];

            array_push($return_array, $temp_arr);

        }


//        $data = DB::select("select play_masters.id, play_masters.barcode_number, play_masters.draw_master_id, play_masters.user_id, play_masters.game_id,
//       play_masters.user_relation_id, play_masters.is_claimed, play_masters.is_cancelled, play_masters.is_cancelable, play_masters.created_at, play_masters.updated_at,
//       draw_masters.draw_name, draw_masters.visible_time from play_masters
//             inner join draw_masters ON draw_masters.id = play_masters.draw_master_id
//             where date(play_masters.created_at) = ? and play_masters.game_id = ".$gameId,[$today]);
//
////        $cpanelReportController =  new CPanelReportController();
//        foreach ($data as $x){
//            $total_prize = $total_prize + (int)$this->get_prize_value_by_barcode($x->id);
//            $total_quantity = $total_quantity + $this->get_total_quantity_by_barcode($x->id);
//        }

//        $return_array = [
//            'total_prize' => $total_prize,
//            'total_quantity' =>$total_quantity
//        ];

        return response()->json(['success'=> 1, 'data' => $return_array], 200);
        // return response()->json(['success'=> $draw_times, 'data' => $return_array], 200);
    }

    public function get_prize_value_by_barcode($play_master_id){

//         $play_master = PlayMaster::findOrFail($play_master_id);
//         $play_master_game_id = $play_master->game_id;
//         $play_game_ids = PlayDetails::where('play_master_id',$play_master_id)->distinct()->pluck('game_type_id');

//         $test = [];
//         $test1 = [];

//         $prize_value = 0;

//         $play_date = Carbon::parse($play_master->created_at)->format('Y-m-d');
//         $result_master = ResultMaster::where('draw_master_id', $play_master->draw_master_id)->where('game_date',$play_date)->whereGameId($play_master_game_id)->first();

//         foreach ($play_game_ids as $game_type_id){
//             $result_details = ResultDetail::whereResultMasterId($result_master->id)->whereGameTypeId($game_type_id)->first();

// //            if($result_details != null){
// //                $prize_value = $prize_value + 0;
// //                continue;
// //            }

//             $checkPlayMaster = PlayDetails::wherePlayMasterId($play_master_id)->whereGameTypeId($game_type_id)->whereCombinationNumberId($result_details->combination_number_id)->first();

//             if($checkPlayMaster != null){
//                 $gameType = GameType::find($game_type_id);
//                 $prize_value = ($prize_value + ($gameType->winning_price * $checkPlayMaster -> quantity)) * $result_details->multiplexer;
//             }else{
//                 $prize_value = $prize_value + 0;
//             }
//         }

//         return $prize_value;

//        return response()->json(['success'=> $prize_value, 'data' => $test], 200);


        $play_master = PlayMaster::findOrFail($play_master_id);
        $play_master_game_id = $play_master->game_id;
        $play_game_ids = PlayDetails::where('play_master_id',$play_master_id)->distinct()->pluck('game_type_id');

        $play_date = Carbon::parse($play_master->created_at)->format('Y-m-d');
        $result_master = ResultMaster::where('draw_master_id', $play_master->draw_master_id)->where('game_date',$play_date)->whereGameId($play_master_game_id)->first();
        $prize_value = 0;
        $result_multiplier = 1;
        $result_number_combination_id = -1;
        foreach ($play_game_ids as $game_id){

            if(!empty($result_master)){
                // $result_number_combination_id = (ResultDetail::whereResultMasterId($result_master->id)->whereGameTypeId($game_id)->first())->combination_number_id;
                $result_number_combination_id = ResultDetail::whereResultMasterId($result_master->id)->whereGameTypeId($game_id)->first();
                // $result_multiplier = (ResultDetail::whereResultMasterId($result_master->id)->whereGameTypeId($game_id)->first())->multiplexer;
                $result_multiplier = (ResultDetail::whereResultMasterId($result_master->id)->whereGameTypeId($game_id)->first());

                // return response()->json(['success'=>$result_master,'data'=> $result_number_combination_id], 200);
                if(empty($result_number_combination_id)){
                    continue;
                }
                if(!empty($result_number_combination_id)){
                    $result_number_combination_id = (int)($result_number_combination_id->combination_number_id);
                    $result_multiplier = $result_multiplier->multiplexer;
                }
            }else{
                $result_number_combination_id = null;
                $result_multiplier = 1;
            }


            if(empty($result_number_combination_id)){
                $result_number_combination_id = -1;
                $result_multiplier = 1;
            }


//            $data = DB::select("select (play_details.quantity* game_types.winning_price) as price_value from play_masters
//                inner join play_details on play_details.play_master_id = play_masters.id
//                inner join game_types on game_types.id = play_details.game_type_id
//                where play_masters.id = ".$play_master_id." and play_details.game_type_id = ".$game_id." and play_details.combination_number_id = ".$result_number_combination_id);

            $data = DB::select("select (sum(quantity) * game_types.winning_price) as price_value from play_details
                inner join play_masters on play_details.play_master_id = play_masters.id
                inner join game_types on game_types.id = play_details.game_type_id
                where play_details.play_master_id = ".$play_master_id."  and date(play_details.created_at) = ?
                and play_masters.draw_master_id = ".$play_master->draw_master_id."
                and play_details.combination_number_id = ".$result_number_combination_id." and game_type_id = ".$game_id."
                group by winning_price",[$play_date]);

//            select (sum(quantity) * game_types.winning_price) as price_value from play_details
//inner join play_masters on play_details.play_master_id = play_masters.id
//inner join game_types on game_types.id = play_details.game_type_id
//where play_details.play_master_id = 864 and date(play_details.created_at) = '2022-06-09' and play_masters.draw_master_id = 448 and play_details.combination_number_id = 100 and game_type_id = 2

            if($data){
                $prize_value = ($data[0]->price_value + $prize_value) * $result_multiplier;
            }


//            else{
//                $prize_value = $prize_value + 0;
//            }

//            if($game_id == 1){
//                $singleGamePrize = PlayMaster::join('play_details','play_masters.id','play_details.play_master_id')
//                    ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
//                    ->join('game_types','play_details.game_type_id','game_types.id')
//                    ->select(DB::raw("max(play_details.quantity)* max(game_types.winning_price) as prize_value") )
//                    ->where('play_masters.id',$play_master_id)
//                    ->where('play_details.game_type_id',$game_id)
//                    ->where('play_details.combination_number_id',$result_number_combination_id)
//                    ->groupBy('number_combinations.single_number_id')
//                    ->first();
//            }
//            if($game_id == 2){
//                $tripleGamePrize = PlayMaster::join('play_details','play_masters.id','play_details.play_master_id')
//                    ->join('number_combinations','play_details.combination_number_id','number_combinations.id')
//                    ->join('game_types','play_details.game_type_id','game_types.id')
//                    ->select(DB::raw("play_details.quantity * game_types.winning_price as prize_value") )
//                    ->where('play_masters.id',$play_master_id)
//                    ->where('play_details.game_type_id',$game_id)
//                    ->where('play_details.combination_number_id',$result_number_combination_id)
//                    ->first();
//            }
        }

//        return ['single' => $singleGamePrize];

//        if(!empty($singleGamePrize)){
//            $prize_value+= $singleGamePrize->prize_value;
//        }
//        if(!empty($tripleGamePrize)){
//            $prize_value+= $tripleGamePrize->prize_value;
//        }
        return $prize_value;
    }

    public function get_total_quantity_by_barcode($play_master_id){
        $play_master = PlayMaster::findOrFail($play_master_id);
        $play_game_ids = PlayDetails::where('play_master_id',$play_master_id)->distinct()->pluck('game_type_id');
//        $total_quantity = 0;
//        foreach ($play_game_ids as $game_id){
//            if($game_id == 1){
//                $singleGameQuantity = DB::select("select sum(quantity) as total_quantity from(select max(quantity) as quantity from play_details
//inner join number_combinations ON number_combinations.id = play_details.combination_number_id
//where play_details.play_master_id=".$play_master_id." and play_details.game_type_id=1
//group by number_combinations.single_number_id) as table1")[0];
//
//            }
//            if($game_id == 2){
//                $tripleGameQuantity = DB::select("select sum(quantity) as total_quantity from play_details
//inner join number_combinations ON number_combinations.id = play_details.combination_number_id
//where play_details.play_master_id=".$play_master_id." and play_details.game_type_id= 2
//group by play_details.play_master_id")[0];
//
//            }
//        }
//
//        if(!empty($singleGameQuantity)){
//            $total_quantity+= $singleGameQuantity->total_quantity;
//        }
//        if(!empty($tripleGameQuantity)){
//            $total_quantity+= $tripleGameQuantity->total_quantity;
//        }
//        return $total_quantity;

        $data = (DB::select("select sum(play_details.quantity) as total_quantity from play_details where play_master_id = ".$play_master_id)[0])->total_quantity;

        return (int)$data;
    }

    public function get_total_amount_by_barcode($play_master_id){
        $play_game_ids = PlayDetails::where('play_master_id',$play_master_id)->distinct()->pluck('game_type_id');
//        $total_amount = 0;
//        foreach ($play_game_ids as $game_id){
//            if($game_id == 1){
//                $singleGameTotalAmount = DB::select("select sum(quantity)*max(mrp) as total_amount from(select max(quantity) as quantity,round(max(mrp)*22) as mrp from play_details
//                inner join number_combinations ON number_combinations.id = play_details.combination_number_id
//                where play_details.play_master_id= ".$play_master_id." and play_details.game_type_id=1
//                group by number_combinations.single_number_id) as table1")[0];
//            }
//            if($game_id == 2){
//                $tripleGameTotalAmount = DB::select("select sum(quantity*mrp) as total_amount from play_details
//                inner join number_combinations ON number_combinations.id = play_details.combination_number_id
//                where play_details.play_master_id= ".$play_master_id." and play_details.game_type_id= 2
//                group by play_details.play_master_id")[0];
//            }
//        }
//
//        if(!empty($singleGameTotalAmount)){
//            $total_amount+= $singleGameTotalAmount->total_amount;
//        }
//        if(!empty($tripleGameTotalAmount)){
//            $total_amount+= $tripleGameTotalAmount->total_amount;
//        }
//        return $total_amount;

        $data = (DB::select("select sum(play_details.quantity* game_types.mrp) as total_mrp from play_details
            inner join game_types on game_types.id = play_details.game_type_id
            where play_details.play_master_id = ".$play_master_id)[0])->total_mrp;

        return $data;
    }

    public function customer_sale_report(){
        $data = DB::select("select table1.play_master_id, table1.terminal_pin, table1.user_name, table1.user_id, table1.stockist_id, table1.total, table1.commission, users.user_name as stokiest_name from (select max(play_master_id) as play_master_id,terminal_pin,user_name,user_id,stockist_id,
        sum(total) as total,round(sum(commission),2) as commission from (
        select max(play_masters.id) as play_master_id,users.user_name,users.email as terminal_pin,
        round(sum(play_details.quantity * play_details.mrp)) as total,
        sum(play_details.quantity * play_details.mrp)* (max(play_details.commission)/100) as commission,
        play_masters.user_id, stockist_to_terminals.stockist_id
        FROM play_masters
        inner join play_details on play_details.play_master_id = play_masters.id
        inner join game_types ON game_types.id = play_details.game_type_id
        inner join users ON users.id = play_masters.user_id
        left join stockist_to_terminals on play_masters.user_id = stockist_to_terminals.terminal_id
        where play_masters.is_cancelled=0
        group by stockist_to_terminals.stockist_id, play_masters.user_id,users.user_name,play_details.game_type_id,users.email) as table1 group by user_name,user_id,terminal_pin,stockist_id) as table1
        left join users on table1.stockist_id = users.id ");

        foreach($data as $x){
            $newPrize = 0;
            $tempntp = 0;
            $newData = PlayMaster::where('user_id',$x->user_id)->get();
            foreach($newData as $y) {
                $tempData = 0;
                $newPrize += $this->get_prize_value_by_barcode($y->id);
                $tempData = (PlayDetails::select(DB::raw("if(game_type_id = 1,(mrp*22)*quantity-(commission/100),mrp*quantity-(commission/100)) as total"))
                    ->where('play_master_id',$y->id)->distinct()->get())[0];
                $tempntp += $tempData->total;
            }
            $detail = (object)$x;
            $detail->prize_value = $newPrize;
            $detail->ntp = $tempntp;
        }
        return response()->json(['success'=> 1, 'data' => $data], 200);
    }

    public function customer_sale_reports(Request $request){
        $requestedData = (object)$request->json()->all();
        $start_date = $requestedData->startDate;
        $end_date = $requestedData->endDate;


//        $data = DB::select("select table1.play_master_id, table1.terminal_pin, table1.user_name, table1.user_id, table1.stockist_id, table1.total, table1.commission, users.user_name as stokiest_name from (select max(play_master_id) as play_master_id,terminal_pin,user_name,user_id,stockist_id,
//        sum(total) as total,round(sum(commission),2) as commission from (
//        select max(play_masters.id) as play_master_id,users.user_name,users.email as terminal_pin,
//        round(sum(play_details.quantity * play_details.mrp)) as total,
//        sum(play_details.quantity * play_details.mrp)* (max(play_details.commission)/100) as commission,
//        play_masters.user_id, stockist_to_terminals.stockist_id
//        FROM play_masters
//        inner join play_details on play_details.play_master_id = play_masters.id
//        inner join game_types ON game_types.id = play_details.game_type_id
//        inner join users ON users.id = play_masters.user_id
//        left join stockist_to_terminals on play_masters.user_id = stockist_to_terminals.terminal_id
//        where play_masters.is_cancelled=0 and date(play_masters.created_at) >= ? and date(play_masters.created_at) <= ?
//        group by stockist_to_terminals.stockist_id, play_masters.user_id,users.user_name,play_details.game_type_id,users.email) as table1 group by user_name,user_id,terminal_pin,stockist_id) as table1
//        left join users on table1.stockist_id = users.id ",[$start_date,$end_date]);

//        $data = DB::select("select table1.play_master_id, table1.terminal_pin, table1.user_name, table1.user_id, table1.stockist_id, table1.total, table1.commission, table1.user_name as stockist_name from (select max(play_master_id) as play_master_id,terminal_pin,user_name,user_id,stockist_id,
//        sum(total) as total,round(sum(commission),2) as commission from (
//        select max(play_masters.id) as play_master_id,users.user_name,users.email as terminal_pin,
//        round(sum(play_details.quantity * play_details.mrp)) as total,
//        sum(play_details.quantity * play_details.mrp)* (max(play_details.commission)/100) as commission,
//        play_masters.user_id, user_relation_with_others.stockist_id
//        FROM play_masters
//        inner join play_details on play_details.play_master_id = play_masters.id
//        inner join game_types ON game_types.id = play_details.game_type_id
//        inner join users ON users.id = play_masters.user_id
//        left join user_relation_with_others on play_masters.user_id = user_relation_with_others.terminal_id
//        where play_masters.is_cancelled=0 and date(play_masters.created_at) >= ? and date(play_masters.created_at) <= ? and user_relation_with_others.active = 1
//        group by user_relation_with_others.stockist_id, play_masters.user_id,users.user_name,play_details.game_type_id,users.email) as table1 group by user_name,user_id,terminal_pin,stockist_id) as table1
//        left join users on table1.stockist_id = users.id",[$start_date,$end_date]);

        $data = DB::select("select table1.play_master_id, table1.terminal_pin, table1.user_name, table1.user_id, table1.stockist_id, table1.total, table1.commission,stockist_commission,super_stockist_commission, table1.user_name as stockist_name from (select max(play_master_id) as play_master_id,terminal_pin,user_name,user_id,stockist_id,
        sum(total) as total,round(sum(commission),2) as commission,round(sum(stockist_commission),2) as stockist_commission,round(sum(super_stockist_commission),2) as super_stockist_commission from (
        select max(play_masters.id) as play_master_id,users.user_name,users.email as terminal_pin,
        round(sum(play_details.quantity * play_details.mrp)) as total,
        sum(play_details.quantity * play_details.mrp)* (max(play_details.commission)/100) as commission,
        sum(play_details.quantity * play_details.mrp)* (max(play_details.stockist_commission)/100) as stockist_commission,
        sum(play_details.quantity * play_details.mrp)* (max(play_details.super_stockist_commission)/100) as super_stockist_commission,
        play_masters.user_id, user_relation_with_others.stockist_id
        FROM play_masters
        inner join play_details on play_details.play_master_id = play_masters.id
        inner join game_types ON game_types.id = play_details.game_type_id
        inner join users ON users.id = play_masters.user_id
        left join user_relation_with_others on play_masters.user_id = user_relation_with_others.terminal_id
        where play_masters.is_cancelled=0 and date(play_masters.created_at) >= ? and date(play_masters.created_at) <= ? and user_relation_with_others.active = 1
        group by user_relation_with_others.stockist_id, play_masters.user_id,users.user_name,play_details.game_type_id,users.email) as table1 group by user_name,user_id,terminal_pin,stockist_id) as table1
        left join users on table1.stockist_id = users.id",[$start_date,$end_date]);

//        return response()->json(['success'=> 1, 'data' => $data], 200);

        foreach($data as $x){
            $newPrizeClaimed = 0;
            $newPrizeUnClaimed = 0;
            $tempntp = 0;
            $tempPrize = 0;
            $newData = PlayMaster::where('user_id',$x->user_id)->whereRaw('date(created_at) >= ?', [$start_date])->whereRaw('date(created_at) <= ?', [$end_date])->get();
            foreach($newData as $y) {
                $tempData = 0;
                $tempPrize += $this->get_prize_value_by_barcode($y->id);
//                if ($tempPrize > 0 && $y->is_claimed == 1) {
                if ($tempPrize > 0) {
                    $newPrizeClaimed += $y->is_claimed == 1? $this->get_prize_value_by_barcode($y->id) : 0;
                    $newPrizeUnClaimed += $y->is_claimed == 0? $this->get_prize_value_by_barcode($y->id) : 0;
                } else {
                    $newPrizeClaimed += 0;
                    $newPrizeUnClaimed += 0;
                }
//                $tempData = (PlayDetails::select(DB::raw("if(game_type_id = 1,(mrp*22)*quantity-(commission/100),mrp*quantity-(commission/100)) as total"))
//                    ->where('play_master_id',$y->id)->distinct()->get())[0];
//                $tempntp += $tempData->total;
            }
            $detail = (object)$x;
            $detail->claimed_prize_value = $newPrizeClaimed;
            $detail->unclaimed_prize_value = $newPrizeUnClaimed;
//            $detail->ntp = $tempntp;
        }
        return response()->json(['success'=> 1, 'data' => $data], 200);



//        return response()->json(['success'=> 1, 'data' => $start_date, 'fdsf'=>$end_date], 200);
    }

    public function load_report(Request $request){

        $requestedData = (object)$request->json()->all();

//        return response()->json(['success'=> 1, 'data' => $requestedData], 200);

        $x = [];

        $today= Carbon::today()->format('Y-m-d');

        if($requestedData->terminal_id === null){

            if($requestedData->game_id === 1){
                $single_number = DB::select("select single_numbers.id, single_numbers.single_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join single_numbers on play_details.combination_number_id = single_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 1 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join single_numbers on tabel1.combination_number_id = single_numbers.id",[$today]);

                $triple_number =  DB::select("select number_combinations.id, number_combinations.visible_triple_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join number_combinations on play_details.combination_number_id = number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 2 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join number_combinations on tabel1.combination_number_id = number_combinations.id",[$today]);

                $double_number =  DB::select("select double_number_combinations.id, double_number_combinations.visible_double_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join double_number_combinations on play_details.combination_number_id = double_number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 5 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join double_number_combinations on tabel1.combination_number_id = double_number_combinations.id",[$today]);

                $temp = [
                    'single_number' => $single_number,
                    'double_number' => $double_number,
                    'triple_number' => $triple_number
                ];

                array_push($x ,(object)$temp);

            }elseif ($requestedData->game_id === 2){

                $twelve_card = DB::select("select card_combinations.id, CONCAT(card_combinations.rank_name, ' ' ,card_combinations.suit_name) as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    inner join card_combinations on play_details.combination_number_id = card_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 3 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join card_combinations on tabel1.combination_number_id = card_combinations.id
                    where card_combinations.card_combination_type_id = 1",[$today]);

                $temp = [
                    'twelve_card' => $twelve_card,
                ];

                array_push($x ,(object)$temp);

            }elseif ($requestedData->game_id === 3){

                $sixteen_card = DB::select("select card_combinations.id, CONCAT(card_combinations.rank_name, ' ' ,card_combinations.suit_name) as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    inner join card_combinations on play_details.combination_number_id = card_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 4 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join card_combinations on tabel1.combination_number_id = card_combinations.id
                    where card_combinations.card_combination_type_id = 2",[$today]);

                $temp = [
                    'sixteen_card' => $sixteen_card,
                ];

                array_push($x ,(object)$temp);

            }elseif ($requestedData->game_id === 4){

                $single_individual = DB::select("select single_numbers.id, single_numbers.single_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join single_numbers on play_details.combination_number_id = single_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 6 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join single_numbers on tabel1.combination_number_id = single_numbers.id",[$today]);

                $temp = [
                    'single_individual' => $single_individual,
                ];

                array_push($x ,(object)$temp);

            }elseif ($requestedData->game_id === 5){

                $double_individual = DB::select("select double_number_combinations.id, double_number_combinations.visible_double_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join double_number_combinations on play_details.combination_number_id = double_number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 7 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join double_number_combinations on tabel1.combination_number_id = double_number_combinations.id",[$today]);

                $andar_number = DB::select("select andar_numbers.id, andar_numbers.andar_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join andar_numbers on play_details.combination_number_id = andar_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 8 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join andar_numbers on tabel1.combination_number_id = andar_numbers.id",[$today]);

                $bahar_number = DB::select("select bahar_numbers.id, bahar_numbers.bahar_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join bahar_numbers on play_details.combination_number_id = bahar_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 9 and play_masters.draw_master_id = ".$requestedData->draw_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join bahar_numbers on tabel1.combination_number_id = bahar_numbers.id
                    order by bahar_numbers.id",[$today]);

                $temp = [
                    'double_individual' => $double_individual,
                    'andar_number' => $andar_number,
                    'bahar_number' => $bahar_number
                ];

                array_push($x ,(object)$temp);
            }


            return response()->json(['success'=> 1, 'data' => $x[0]], 200);
        }


        if($requestedData->game_id === 1){
            $single_number = DB::select("select single_numbers.id, single_numbers.single_number as visible_number, tabel1.quantity from (
            select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
            right join single_numbers on play_details.combination_number_id = single_numbers.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where game_type_id = 1 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
            group by play_details.combination_number_id) as tabel1
            right join single_numbers on tabel1.combination_number_id = single_numbers.id",[$today]);

            $triple_number =  DB::select("select number_combinations.id, number_combinations.visible_triple_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join number_combinations on play_details.combination_number_id = number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 2 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join number_combinations on tabel1.combination_number_id = number_combinations.id",[$today]);

            $double_number =  DB::select("select double_number_combinations.id, double_number_combinations.visible_double_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join double_number_combinations on play_details.combination_number_id = double_number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 5 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join double_number_combinations on tabel1.combination_number_id = double_number_combinations.id",[$today]);

            $temp = [
                'single_number' => $single_number,
                'double_number' => $double_number,
                'triple_number' => $triple_number
            ];

            array_push($x ,(object)$temp);
        }elseif ($requestedData->game_id === 2){

            $twelve_card = DB::select("select card_combinations.id, CONCAT(card_combinations.rank_name, ' ' ,card_combinations.suit_name) as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    inner join card_combinations on play_details.combination_number_id = card_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 3 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join card_combinations on tabel1.combination_number_id = card_combinations.id
                    where card_combinations.card_combination_type_id = 1",[$today]);

            $temp = [
                'twelve_card' => $twelve_card,
            ];

            array_push($x ,(object)$temp);

        }elseif ($requestedData->game_id === 3){

            $sixteen_card = DB::select("select card_combinations.id, CONCAT(card_combinations.rank_name, ' ' ,card_combinations.suit_name) as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    inner join card_combinations on play_details.combination_number_id = card_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 4 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join card_combinations on tabel1.combination_number_id = card_combinations.id
                    where card_combinations.card_combination_type_id = 2",[$today]);

            $temp = [
                'sixteen_card' => $sixteen_card,
            ];

            array_push($x ,(object)$temp);

        }elseif ($requestedData->game_id === 4){

            $single_individual = DB::select("select single_numbers.id, single_numbers.single_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join single_numbers on play_details.combination_number_id = single_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 6 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join single_numbers on tabel1.combination_number_id = single_numbers.id",[$today]);

            $temp = [
                'single_individual' => $single_individual,
            ];

            array_push($x ,(object)$temp);

        }elseif ($requestedData->game_id === 5){

            $double_individual = DB::select("select double_number_combinations.id, double_number_combinations.visible_double_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join double_number_combinations on play_details.combination_number_id = double_number_combinations.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 7 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join double_number_combinations on tabel1.combination_number_id = double_number_combinations.id",[$today]);

            $andar_number = DB::select("select andar_numbers.id, andar_numbers.andar_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join andar_numbers on play_details.combination_number_id = andar_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 8 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join andar_numbers on tabel1.combination_number_id = andar_numbers.id",[$today]);

            $bahar_number = DB::select("select bahar_numbers.id, bahar_numbers.bahar_number as visible_number, tabel1.quantity from (
                    select sum(play_details.quantity) as quantity, play_details.combination_number_id from play_details
                    right join bahar_numbers on play_details.combination_number_id = bahar_numbers.id
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 9 and play_masters.draw_master_id = ".$requestedData->draw_id." and play_masters.user_id = ".$requestedData->terminal_id." and date(play_masters.created_at) = ?
                    group by play_details.combination_number_id) as tabel1
                    right join bahar_numbers on tabel1.combination_number_id = bahar_numbers.id
                    order by bahar_numbers.id",[$today]);

            $temp = [
                'double_individual' => $double_individual,
                'andar_number' => $andar_number,
                'bahar_number' => $bahar_number
            ];

            array_push($x ,(object)$temp);
        }


        return response()->json(['success'=> 1, 'data' => $x[0]], 200);



        //        $playMasterControllerObj = new PlayMasterController();
//        $lastDrawId = (DrawMaster::whereActive(1)->first())->id;

//        $lastDrawId1 = (NextGameDraw::whereGameId(1)->first())->last_draw_id;
//        $lastDrawId2 = (NextGameDraw::whereGameId(2)->first())->last_draw_id;
//        $lastDrawId3 = (NextGameDraw::whereGameId(3)->first())->last_draw_id;
//        $lastDrawId4 = (NextGameDraw::whereGameId(4)->first())->last_draw_id;
//        $lastDrawId5 = (NextGameDraw::whereGameId(5)->first())->last_draw_id;

//        $totalSale = $playMasterControllerObj->get_total_sale_by_game($today,$lastDrawId,1);

//        $games = Game::get();
//        $gameTypes = GameType::get();
//        $x = [];
//        $temp = [];

//        $users = User::whereUserTypeId(5)->get();
//
//        foreach ($users as $user){
////            foreach ($gameTypes as $gameType){
////                $temp = [];
//                $temp = [
//                    'terminal_name' => $user->user_name,
////                    'game_name' => $gameType->game_type_name,
////                    'total_sale' => $playMasterControllerObj->get_total_sale_by_terminal($today,$lastDrawId,$user->id),
//                    'total_sale' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,1,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,2,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId2,3,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId3,4,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,5,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId4,6,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,7,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,8,$user->id)
//                        + $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,9,$user->id),
//                    'single' =>  $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,1,$user->id),
//                    'triple' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,2,$user->id),
//                    'twelve_card' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId2,3,$user->id),
//                    'sixteen_card' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId3,4,$user->id),
//                    'double' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId1,5,$user->id),
//                    'singleI' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId4,6,$user->id),
//                    'doubleI' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,7,$user->id),
//                    'andar' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,8,$user->id),
//                    'bahar' => $playMasterControllerObj->get_total_sale_by_gameType($today,$lastDrawId5,9,$user->id),
//                ];
//                array_push($x, (object)$temp);
////            }
//        }

        return response()->json(['success'=> 1, 'data' => 0], 200);
    }
}
