<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DoubleNumberCombination;
use App\Models\Game;
use App\Models\GameType;
use App\Models\ManualResult;
use App\Models\NumberCombination;
use App\Models\PlayDetails;
use App\Models\PlayMaster;
use App\Models\ResultDetail;
use App\Models\ResultMaster;
use App\Models\SingleNumber;
use Carbon\Carbon;
use http\Client\Curl\User;
use Illuminate\Http\Request;
use App\Models\NextGameDraw;
use App\Models\DrawMaster;
use App\Http\Controllers\ManualResultController;
use App\Http\Controllers\NumberCombinationController;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Scalar\String_;
//use App\Models\User;

class CentralController extends Controller
{

    public function createAutoResult($id){
        $this->createResult($id, 1);
    }

    public function createResult($id, $count){

        $game = Game::find($id);
        if(!$game){
            return response()->json(['success'=>0, 'message' => 'Incorrect Game Id'], 200);
        }
        if($game->active == "no"){
            return response()->json(['success'=>0, 'message' => 'Game not active'], 200);
        }

        $today= Carbon::today()->format('Y-m-d');
        $playMasterControllerObj = new PlayMasterController();
        $resultMasterControllerObj = new ResultMasterController();

        $game_multiplexer = (GameType::whereGameId($id)->first())->multiplexer;

        if($game_multiplexer == 1){
            $tempM= [1,2,3];
            $game_multiplexer = $tempM[array_rand($tempM)];
        }

        $ManualGameCheck = ManualResult::whereGameDate($today)->whereGameTypeId((GameType::whereGameId($id)->first())->id)->first();
        if($ManualGameCheck){
            $game_multiplexer = $ManualGameCheck->multiplexer;
        }

        if($id == 1){

            $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
            $nextDrawId = $nextGameDrawObj->next_draw_id;
            $lastDrawId = $nextGameDrawObj->last_draw_id;

            //payouts
            $singleNumber = (GameType::find(1));
            $doubleNumber = (GameType::find(5));
            $tripleNumber = (GameType::find(2));

            //total sales
            $singleNumberTotalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId,1);
            $doubleNumberTotalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId,5);
            $tripleNumberTotalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId,2);

//        $allGameTotalSale = 1200;
//            $allGameTotalSale = (($singleNumberTotalSale*($singleNumber->payout))/100) + (($doubleNumberTotalSale*($doubleNumber->payout))/100) + (($tripleNumberTotalSale*($tripleNumber->payout))/100);
            $allGameTotalSale = ((($singleNumberTotalSale*($singleNumber->payout))/100) + (($doubleNumberTotalSale*($doubleNumber->payout))/100) + (($tripleNumberTotalSale*($tripleNumber->payout))/100))/($game_multiplexer);

            //triple number
            $tripleValue = (int)($allGameTotalSale/($tripleNumber->winning_price));
            $loopOn = 1;

//            $tripleNumberTargetData = DB::select("select * from play_details
//            inner join play_masters on play_details.play_master_id = play_masters.id
//            where quantity <= ? and game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//            order by quantity desc
//            limit 1",[$tripleValue, $today, $lastDrawId]);
//
//            if(empty($tripleNumberTargetData)) {
//                $tripleNumberTargetData = DB::select("select id as combination_number_id, 0 as quantity from single_numbers
//                    where id not in (select combination_number_id from play_details
//                    inner join play_masters on play_details.play_master_id = play_masters.id
//                    where game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
//                    order by RAND()
//                    limit 1",[$today, $lastDrawId]);
//            }
//
//            if(empty($tripleNumberTargetData)){
//                $tripleNumberTargetData = DB::select("select * from play_details
//            inner join play_masters on play_details.play_master_id = play_masters.id
//            where quantity > ? and game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//            order by quantity
//            limit 1",[$tripleValue, $today, $lastDrawId]);
//            }

            $resultToBeSaved = [];

            while(true){

//                $tripleNumberTargetData = DB::select("select * from play_details
//                inner join number_combinations on number_combinations.id = play_details.combination_number_id
//                inner join play_masters on play_details.play_master_id = play_masters.id
//                where quantity <= ? and game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//                order by quantity desc
//                ",[$tripleValue, $today, $lastDrawId]);

                $tripleNumberTargetData = DB::select("select * from play_details
                inner join number_combinations on number_combinations.id = play_details.combination_number_id
                inner join play_masters on play_details.play_master_id = play_masters.id
                where quantity <= ? and game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                order by RAND(), quantity desc
                ",[$tripleValue, $today, $lastDrawId]);

                if(empty($tripleNumberTargetData)) {
                    $tripleNumberTargetData = DB::select("select id as combination_number_id, visible_triple_number , 0 as quantity from number_combinations
                        where id not in (select combination_number_id from play_details
                        inner join play_masters on play_details.play_master_id = play_masters.id
                        where game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
                        order by RAND()
                        ",[$today, $lastDrawId]);
                }

                if(empty($tripleNumberTargetData)){
                    $tripleNumberTargetData = DB::select("select * from play_details
                inner join number_combinations on number_combinations.id = play_details.combination_number_id
                inner join play_masters on play_details.play_master_id = play_masters.id
                where quantity > ? and game_type_id = 2 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                order by quantity
                limit 1",[$tripleValue, $today, $lastDrawId]);
                }

                foreach ($tripleNumberTargetData as $tripleData){
                    $splitNumber = str_split($tripleData->visible_triple_number);
                    $singleNumberValue = (SingleNumber::select()->whereSingleNumber($splitNumber[2])->first())->id;
                    $doubleNumberValue = (DoubleNumberCombination::select()->whereDoubleNumber($splitNumber[1].$splitNumber[2])->first())->id;
                    $doubleNumberQuantity = 0;
                    $singleNumberQuantity = 0;

                    $doubleNumberTargetData = DB::select("select ifnull(sum(quantity),0) as quantity from play_details
                        inner join play_masters on play_details.play_master_id = play_masters.id
                        where play_details.combination_number_id = ? and game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                        ",[$doubleNumberValue, $today, $lastDrawId]);

                    if(empty($doubleNumberTargetData)) {
                        $doubleNumberQuantity = 0;
                    }else{
                        $doubleNumberQuantity = $doubleNumberTargetData[0]->quantity;
                    }

                    $singleNumberTargetData = DB::select("select ifnull(sum(quantity),0) as quantity from play_details
                        inner join play_masters on play_details.play_master_id = play_masters.id
                        where play_details.combination_number_id = ?  and game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                       ",[$singleNumberValue, $today, $lastDrawId]);

                    if(empty($singleNumberTargetData)) {
                        $singleNumberQuantity = 0;
                    }else{
                        $singleNumberQuantity = $singleNumberTargetData[0]->quantity;
                    }

                    $totalSale = (($tripleData->quantity) * $tripleNumber->winning_price) + ($doubleNumberQuantity * $doubleNumber->winning_price) + ($singleNumberQuantity * $singleNumber->winning_price);

                    if($totalSale <= $allGameTotalSale){
                        $loopOn = 0;
                        $temp = [
                            'single x' => $splitNumber[2],
                            'single' => $singleNumberValue,
                            'single quantity' => $singleNumberQuantity,
                            'double' => $doubleNumberValue,
                            'double quantity' => $doubleNumberQuantity,
                            'triple' => $tripleData->combination_number_id,
//                            'tripleNumberData' => $tripleNumberTargetData,
                            'total_sale' => $totalSale,
                            'allGameTotalSale' => $allGameTotalSale,
                            '$tripleData' => $tripleData,
                            '$splitNumber' => $splitNumber,
                        ];

                        $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,1,$singleNumberValue,$game_multiplexer))->content(),true);
                        $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,2,$tripleData->combination_number_id,$game_multiplexer))->content(),true);
                        $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,5,$doubleNumberValue,$game_multiplexer))->content(),true);

                        array_push($resultToBeSaved, $temp);
                        break;
                    }
                }

                if($loopOn == 1){
                    if($tripleValue > 0 ){
                        $tripleValue = $tripleValue - 1;
                        continue;
                    }else{
                        break;
                    }
                }else{
                    break;
                }
            }

//            return response()->json(['success'=>1, 'message' => $resultToBeSaved], 200);





//            if(empty($tripleNumberTargetData)) {
//                $tripleNumberAmount = (0) * $tripleNumber->winning_price;
//            }else{
//                $tripleNumberAmount = ($tripleNumberTargetData[0]->quantity) * $tripleNumber->winning_price;
//            }
//
//            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,2,$tripleNumberTargetData[0]->combination_number_id))->content(),true);
//
//            if($playMasterSaveCheck['success'] == 0){
//                return response()->json(['success'=>0, 'message' => 'Save error triple number'], 401);
//            }
//
//
//            //double number
//            $doubleValue = (int)(($allGameTotalSale - $tripleNumberAmount)/($doubleNumber->winning_price));
//            $doubleNumberTargetData = DB::select("select * from play_details
//            inner join play_masters on play_details.play_master_id = play_masters.id
//            where quantity <= ? and game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//            order by quantity desc
//            limit 1",[$doubleValue, $today, $lastDrawId]);
//
//            if(empty($doubleNumberTargetData)) {
//                $doubleNumberTargetData = DB::select("select id as combination_number_id, 0 as quantity from single_numbers
//                    where id not in (select combination_number_id from play_details
//                    inner join play_masters on play_details.play_master_id = play_masters.id
//                    where game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
//                    order by RAND()
//                    limit 1",[$today, $lastDrawId]);
//            }
//
//            if(empty($doubleNumberTargetData)){
//                $doubleNumberTargetData = DB::select("select * from play_details
//            inner join play_masters on play_details.play_master_id = play_masters.id
//            where quantity > ? and game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//            order by quantity
//            limit 1",[$doubleValue, $today, $lastDrawId]);
//            }
//
//            if(empty($doubleNumberTargetData)) {
//                $doubleNumberAmount = (0) * $doubleNumber->winning_price;
//            }else{
//                $doubleNumberAmount = ($doubleNumberTargetData[0]->quantity) * $doubleNumber->winning_price;
//            }
//
//            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,5,$doubleNumberTargetData[0]->combination_number_id))->content(),true);
//
//            if($playMasterSaveCheck['success'] == 0){
//                return response()->json(['success'=>0, 'message' => 'Save error double number'], 401);
//            }
//
//
//            //single number
//            $singleValue = (int)(($allGameTotalSale - ($tripleNumberAmount + $doubleNumberAmount))/($singleNumber->winning_price));
//            $singleNumberTargetData = DB::select("select * from play_details
//                inner join play_masters on play_details.play_master_id = play_masters.id
//                where quantity <= ? and game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//                order by quantity desc
//                limit 1",[$singleValue, $today, $lastDrawId]);
//
//            //empty check
//            if(empty($singleNumberTargetData)) {
//                $singleNumberTargetData = DB::select("select id as combination_number_id, 0 as quantity from single_numbers
//                    where id not in (select combination_number_id from play_details
//                    inner join play_masters on play_details.play_master_id = play_masters.id
//                    where game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
//                    order by RAND()
//                    limit 1",[$today, $lastDrawId]);
//            }
//
//            // greater target value
//            if(empty($singleNumberTargetData)){
//                $singleNumberTargetData = DB::select("select * from play_details
//                    inner join play_masters on play_details.play_master_id = play_masters.id
//                    where quantity > ? and game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
//                    order by quantity
//                    limit 1",[$singleValue, $today, $lastDrawId]);
//            }
//
//            if(empty($singleNumberTargetData)) {
//                $singleNumberAmount = (0) * $singleNumber->winning_price;
//            }else{
//                $singleNumberAmount = ($singleNumberTargetData[0]->quantity) * $singleNumber->winning_price;
//            }
//
//            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,1,$singleNumberTargetData[0]->combination_number_id))->content(),true);
//
//            if($playMasterSaveCheck['success'] == 0){
//                return response()->json(['success'=>0, 'message' => 'Save error single number'], 401);
//            }



//            return response()->json(['single_number'=>$singleNumberTotalSale
//                , 'double_number' => $doubleNumberTotalSale
//                , 'triple_number' => $tripleNumberTotalSale
//                , 'totalSale' => $allGameTotalSale
//                , 'tripleValue' => $tripleValue
//                , 'tripleAmount' => $tripleNumberAmount
//                , 'tripleTargetData' => $tripleNumberTargetData
//                , 'doubleValue' => $doubleValue
//                , 'doubleAmount' => $doubleNumberAmount
//                , 'singleValue' => $singleValue
//                , 'singleAmount' => $singleNumberAmount
//                , 'returnCheck' => $playMasterSaveCheck['success']
//            ], 200);

        }

        if($id == 2){

            $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
            $nextDrawId = $nextGameDrawObj->next_draw_id;
            $lastDrawId = $nextGameDrawObj->last_draw_id;

            $totalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId,3);
            $gameType = GameType::find(3);
            $payout = (($totalSale * ($gameType->payout)) / 100)/ $game_multiplexer;
            $targetValue = floor($payout / $gameType->winning_price);

//            return response()->json(['success'=>$nextGameDrawObj, 'message' => 'Result added'], 200);

            $result = DB::select(DB::raw("select card_combinations.id as card_combination_id,
                sum(play_details.quantity) as total_quantity
                from play_details
                inner join play_masters ON play_masters.id = play_details.play_master_id
                inner join card_combinations ON card_combinations.id = play_details.combination_number_id
                where play_details.game_type_id = 3 and card_combinations.card_combination_type_id = 1 and play_masters.draw_master_id = $lastDrawId and date(play_details.created_at)= " . "'" . $today . "'" . "
                group by card_combinations.id
                having sum(play_details.quantity)<= $targetValue
                order by rand() limit 1"));

            if (empty($result)) {
                // empty value
                $result = DB::select(DB::raw("SELECT card_combinations.id as card_combination_id
                    FROM card_combinations
                    WHERE card_combination_type_id = 1 and card_combinations.id NOT IN(SELECT DISTINCT
                    play_details.combination_number_id FROM play_details
                    INNER JOIN play_masters on play_details.play_master_id= play_masters.id
                    WHERE  play_details.game_type_id=3 and DATE(play_masters.created_at) = " . "'" . $today . "'" . " and play_masters.draw_master_id = $lastDrawId)
                    ORDER by rand() LIMIT 1"));
            }

            if (empty($result)) {
                $result = DB::select(DB::raw("select card_combinations.id as card_combination_id,
                    sum(play_details.quantity) as total_quantity
                    from play_details
                    inner join play_masters ON play_masters.id = play_details.play_master_id
                    inner join card_combinations ON card_combinations.id = play_details.combination_number_id
                    where  play_details.game_type_id=3 and card_combination_type_id = 1 and play_masters.draw_master_id = $lastDrawId and date(play_details.created_at)= " . "'" . $today . "'" . "
                    group by card_combinations.id
                    having sum(play_details.quantity)>= $targetValue
                    order by rand() limit 1"));
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,3,$result[0]->card_combination_id,$game_multiplexer))->content(),true);

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error 12 Card'], 401);
            }
        }

        if($id == 3){

            $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
            $nextDrawId = $nextGameDrawObj->next_draw_id;
            $lastDrawId = $nextGameDrawObj->last_draw_id;

            $totalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId,3);
            $gameType = GameType::find(4);
            $payout = (($totalSale * ($gameType->payout)) / 100)/$game_multiplexer;
            $targetValue = floor($payout / $gameType->winning_price);

            $result = DB::select(DB::raw("select card_combinations.id as card_combination_id,
                sum(play_details.quantity) as total_quantity
                from play_details
                inner join play_masters ON play_masters.id = play_details.play_master_id
                inner join card_combinations ON card_combinations.id = play_details.combination_number_id
                where play_details.game_type_id = 4 and card_combinations.card_combination_type_id = 2 and play_masters.draw_master_id = $lastDrawId and date(play_details.created_at)= " . "'" . $today . "'" . "
                group by card_combinations.id
                having sum(play_details.quantity)<= $targetValue
                order by rand() limit 1"));

            if (empty($result)) {
                // empty value
                $result = DB::select(DB::raw("SELECT card_combinations.id as card_combination_id
                    FROM card_combinations
                    WHERE card_combination_type_id = 2 and card_combinations.id NOT IN(SELECT DISTINCT
                    play_details.combination_number_id FROM play_details
                    INNER JOIN play_masters on play_details.play_master_id= play_masters.id
                    WHERE  play_details.game_type_id=4 and DATE(play_masters.created_at) = " . "'" . $today . "'" . " and play_masters.draw_master_id = $lastDrawId)
                    ORDER by rand() LIMIT 1"));
            }

            if (empty($result)) {
                $result = DB::select(DB::raw("select card_combinations.id as card_combination_id,
                    sum(play_details.quantity) as total_quantity
                    from play_details
                    inner join play_masters ON play_masters.id = play_details.play_master_id
                    inner join card_combinations ON card_combinations.id = play_details.combination_number_id
                    where  play_details.game_type_id=4 and card_combination_type_id = 2 and play_masters.draw_master_id = $lastDrawId and date(play_details.created_at)= " . "'" . $today . "'" . "
                    group by card_combinations.id
                    having sum(play_details.quantity)>= $targetValue
                    order by rand() limit 1"));
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,4,$result[0]->card_combination_id,$game_multiplexer))->content(),true);

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error 16 Card'], 401);
            }

//            return response()->json(['success'=>1, 'message' => 'Result added'], 200);
        }

        if($id == 4){

            $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
            $nextDrawId = $nextGameDrawObj->next_draw_id;
            $lastDrawId = $nextGameDrawObj->last_draw_id;

            $singleNumber = (GameType::find(6));

            $singleValue = (($playMasterControllerObj->get_total_sale($today,$lastDrawId,6) * (($singleNumber->payout)/100))/$game_multiplexer)/($singleNumber->winning_price);

            $singleNumberTargetData = DB::select("select * from play_details
                inner join play_masters on play_details.play_master_id = play_masters.id
                where quantity <= ? and game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                order by quantity desc
                limit 1",[$singleValue, $today, $lastDrawId]);

            //empty check
            if(empty($singleNumberTargetData)) {
                $singleNumberTargetData = DB::select("select id as combination_number_id, 0 as quantity from single_numbers
                    where id not in (select combination_number_id from play_details
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
                    order by RAND()
                    limit 1",[$today, $lastDrawId]);
            }

            // greater target value
            if(empty($singleNumberTargetData)){
                $singleNumberTargetData = DB::select("select * from play_details
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where quantity > ? and game_type_id = 1 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                    order by quantity
                    limit 1",[$singleValue, $today, $lastDrawId]);
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,6,$singleNumberTargetData[0]->combination_number_id,$game_multiplexer))->content(),true);

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error single number'], 401);
            }
        }

        if($id == 5){

            $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
            $nextDrawId = $nextGameDrawObj->next_draw_id;
            $lastDrawId = $nextGameDrawObj->last_draw_id;

            $doubleNumber = (GameType::find(7));

            $doubleValue = (($playMasterControllerObj->get_total_sale($today,$lastDrawId,7) * ($doubleNumber->payout)/100)/$game_multiplexer)/($doubleNumber->winning_price);

            $doubleNumberTargetData = DB::select("select * from play_details
            inner join play_masters on play_details.play_master_id = play_masters.id
            where quantity <= ? and game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
            order by quantity desc
            limit 1",[$doubleValue, $today, $lastDrawId]);

            if(empty($doubleNumberTargetData)) {
                $doubleNumberTargetData = DB::select("select id as combination_number_id, 0 as quantity from single_numbers
                    where id not in (select combination_number_id from play_details
                    inner join play_masters on play_details.play_master_id = play_masters.id
                    where game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?)
                    order by RAND()
                    limit 1",[$today, $lastDrawId]);
            }

            if(empty($doubleNumberTargetData)){
                $doubleNumberTargetData = DB::select("select * from play_details
                inner join play_masters on play_details.play_master_id = play_masters.id
                where quantity > ? and game_type_id = 5 and date(play_details.created_at) = ? and play_masters.draw_master_id = ?
                order by quantity
                limit 1",[$doubleValue, $today, $lastDrawId]);
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,7,$doubleNumberTargetData[0]->combination_number_id,$game_multiplexer))->content(),true);

//            $getResultSingle = (DoubleNumberCombination::find($doubleNumberTargetData[0]->combination_number_id))->andar_number_id;

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error double number'], 401);
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,8,(DoubleNumberCombination::find($doubleNumberTargetData[0]->combination_number_id))->andar_number_id,$game_multiplexer))->content(),true);

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error andar number'], 401);
            }

            $playMasterSaveCheck = json_decode(($resultMasterControllerObj->save_auto_result($lastDrawId,9,(DoubleNumberCombination::find($doubleNumberTargetData[0]->combination_number_id))->bahar_number_id,$game_multiplexer))->content(),true);

            if($playMasterSaveCheck['success'] == 0){
                return response()->json(['success'=>0, 'message' => 'Save error bahar number'], 401);
            }
        }


        $resultMaster = ResultMaster::whereGameDate($today)->whereDrawMasterId($lastDrawId)->first();
        if($resultMaster === null){
            if($count < 2){
                $this->createResult($id, 2);
            }
            $nPlay = PlayMaster::whereDrawMasterId($lastDrawId)->whereDate('created_at',$today)->get();
            foreach ($nPlay as $x){
                $playMasterControllerObj->cancelPlayBYPlayMaster($x->id);
            }
        }


        $tempDrawMasterLastDraw = DrawMaster::whereId($lastDrawId)->whereGameId($id)->first();
        $tempDrawMasterLastDraw->active = 0;
        $tempDrawMasterLastDraw->is_draw_over = 'yes';
        $tempDrawMasterLastDraw->update();

        $tempDrawMasterNextDraw = DrawMaster::whereId($nextDrawId)->whereGameId($id)->first();
        $tempDrawMasterNextDraw->active = 1;
        $tempDrawMasterNextDraw->update();

        $totalDraw = DrawMaster::whereGameId($id)->count();
        $gameCountLastDraw = DrawMaster::whereGameId($id)->where('id', '<=', $lastDrawId)->count();
        $gameCountNextDraw = DrawMaster::whereGameId($id)->where('id', '<=', $nextDrawId)->count();

        if($gameCountNextDraw==$totalDraw){
            $nextDrawId = (DrawMaster::whereGameId($id)->first())->id;
        }
        else {
            $nextDrawId = $nextDrawId + 1;
        }

        if($gameCountLastDraw==$totalDraw){
            $lastDrawId = (DrawMaster::whereGameId($id)->first())->id;
        }
        else{
            $lastDrawId = $lastDrawId + 1;
        }

        $nextGameDrawObj->next_draw_id = $nextDrawId;
        $nextGameDrawObj->last_draw_id = $lastDrawId;
        $nextGameDrawObj->save();

        $tempPlayMaster = PlayMaster::select()->where('is_cancelable',1)->whereGameId($id)->get();
        foreach ($tempPlayMaster as $x){
            $y = PlayMaster::find($x->id);
            $y->is_cancelable = 0;
            $y->update();
        }

        $gameTypes = GameType::whereGameId($id)->get();
        foreach ($gameTypes as $x){
            $y = GameType::find($x->id);
            $y->multiplexer = 1;
            $y->save();
        }

        $terminalController = new TerminalController();
        $terminalController->claimPrizes();

//        $users = DB::select("select * from users where auto_claim = 1");
//        foreach ($users as $x){
//            $y = PlayMaster::whereUserId($x->id)->get();
//            foreach ($y as $z){
//                $playMasterControllerObject = new PlayMasterController();
//                $playMasterControllerObject->claimPrizes($z->id);
//            }
//        }


        return response()->json(['success'=>1, 'message' => 'Result added'], 200);

//        return response()->json(['success'=>0, 'message' => 'Error Occurred'], 400);


//        $playMasterObj = new TerminalReportController();
//        $playMasterObj->updateCancellation();
//
//        $totalSale = $playMasterControllerObj->get_total_sale($today,$lastDrawId);
//        $single = GameType::find(1);
//
////        return response()->json(['success'=>0, 'message' => $totalSale], 401);
//
//        $payout = ($totalSale*($single->payout))/100;
//        $targetValue = floor($payout/$single->winning_price);
//
//        // result less than equal to target value
//        $result = DB::select(DB::raw("select single_numbers.id as single_number_id,single_numbers.single_number,sum(play_details.quantity) as total_quantity  from play_details
//        inner join play_masters ON play_masters.id = play_details.play_master_id
//        inner join single_numbers ON single_numbers.id = play_details.single_number_id
//        where play_masters.draw_master_id = $lastDrawId  and date(play_details.created_at)= "."'".$today."'"."
//        group by single_numbers.single_number,single_numbers.id
//        having sum(play_details.quantity)<= $targetValue
//        order by rand() limit 1"));
//
//        // select empty item for result
//        if(empty($result)){
//            // empty value
//            $result = DB::select(DB::raw("SELECT single_numbers.id as single_number_id FROM single_numbers WHERE id NOT IN(SELECT DISTINCT
//        play_details.single_number_id FROM play_details
//        INNER JOIN play_masters on play_details.play_master_id= play_masters.id
//        WHERE  DATE(play_masters.created_at) = "."'".$today."'"." and play_masters.draw_master_id = $lastDrawId) ORDER by rand() LIMIT 1"));
//        }
//
//        // result greater than equal to target value
//
//        if(empty($result)){
//            $result = DB::select(DB::raw("select single_numbers.id as single_number_id,single_numbers.single_number,sum(play_details.quantity) as total_quantity  from play_details
//            inner join play_masters ON play_masters.id = play_details.play_master_id
//            inner join single_numbers ON single_numbers.id = play_details.single_number_id
//            where play_masters.draw_master_id= $lastDrawId  and date(play_details.created_at)= "."'".$today."'"."
//            group by single_numbers.single_number,single_numbers.id
//            having sum(play_details.quantity)> $targetValue
//            order by rand() limit 1"));
//        }
//
//        $single_number_result_id = $result[0]->single_number_id;
//
//        DrawMaster::query()->update(['active' => 0]);
//        if(!empty($nextGameDrawObj)){
//            DrawMaster::findOrFail($nextDrawId)->update(['active' => 1]);
//        }
//
//
//        $resultMasterController = new ResultMasterController();
//        $jsonData = $resultMasterController->save_auto_result($lastDrawId,$single_number_result_id);
//
//        $resultCreatedObj = json_decode($jsonData->content(),true);
//
//
//        if( !empty($resultCreatedObj) && $resultCreatedObj['success']==1){
//
//            $totalDraw = DrawMaster::count();
//            if($nextDrawId==$totalDraw){
//                $nextDrawId = 1;
//            }
//            else {
//                $nextDrawId = $nextDrawId + 1;
//            }
//
//            if($lastDrawId==$totalDraw){
//                $lastDrawId = 1;
//            }
//            else{
//                $lastDrawId = $lastDrawId + 1;
//            }
//
//            $nextGameDrawObj->next_draw_id = $nextDrawId;
//            $nextGameDrawObj->last_draw_id = $lastDrawId;
//            $nextGameDrawObj->save();
//
//            return response()->json(['success'=>1, 'message' => 'Result added'], 200);
//        }else{
//            return response()->json(['success'=>0, 'message' => 'Result not added'], 401);
//        }

    }

//    public function createResult($id){
//
//        $game = Game::whereId($id)->first();
//        if($game->active === 'no'){
//            return response()->json(['success'=>0, 'message' => 'game not active'], 401);
//        }
//
//        $nextGameDrawObj = NextGameDraw::whereGameId($id)->first();
//        $nextDrawId = $nextGameDrawObj->next_draw_id;
//        $lastDrawId = $nextGameDrawObj->last_draw_id;
//
//        if(!empty($nextGameDrawObj)){
//
//            $tempDrawMaster = new DrawMaster();
//            $tempDrawMasterLastDraw = DrawMaster::whereId($lastDrawId)->whereGameId($id)->first();
//            $tempDrawMasterLastDraw->active = 0;
//            $tempDrawMasterLastDraw->is_draw_over = 'yes';
//            $tempDrawMasterLastDraw->update();
//
//            $tempDrawMasterNextDraw = DrawMaster::whereId($nextDrawId)->whereGameId($id)->first();
//            $tempDrawMasterNextDraw->active = 1;
//            $tempDrawMasterNextDraw->update();
//
//        }
//
//        $resultMasterController = new ResultMasterController();
//        $jsonData = $resultMasterController->save_auto_result($lastDrawId);
//
//        $resultCreatedObj = json_decode($jsonData->content(),true);
//
//        if( !empty($resultCreatedObj) && $resultCreatedObj['success']==1){
//
//            $totalDraw = DrawMaster::whereGameId($id)->count();
//            $gameCountLastDraw = DrawMaster::whereGameId($id)->where('id', '<=', $lastDrawId)->count();
//            $gameCountNextDraw = DrawMaster::whereGameId($id)->where('id', '<=', $nextDrawId)->count();
//
//            if($gameCountNextDraw==$totalDraw){
//                $nextDrawId = (DrawMaster::whereGameId($id)->first())->id;
//            }
//            else {
//                $nextDrawId = $nextDrawId + 1;
//            }
//
//            if($gameCountLastDraw==$totalDraw){
//                $lastDrawId = (DrawMaster::whereGameId($id)->first())->id;
//            }
//            else{
//                $lastDrawId = $lastDrawId + 1;
//            }
//
//            $nextGameDrawObj->next_draw_id = $nextDrawId;
//            $nextGameDrawObj->last_draw_id = $lastDrawId;
//            $nextGameDrawObj->save();
//
//            $tempPlayMaster = PlayMaster::select()->where('is_cancelable',1)->whereGameId($id)->get();
//            foreach ($tempPlayMaster as $x){
//                $y = PlayMaster::find($x->id);
//                $y->is_cancelable = 0;
//                $y->update();
//            }
//
//            return response()->json(['success'=>1, 'message' => 'Result added'], 200);
//        }else{
//            return response()->json(['success'=>0, 'message' => 'Result not added'], 401);
//        }
//
//    }

    public function update_is_draw_over(){
        $data = DrawMaster::whereIsDrawOver('yes')->get();
        foreach($data as $x){
            $y = DrawMaster::find($x->id);
            $y->is_draw_over = 'no';
            $y->update();
        }
        return response()->json(['success'=>1, 'message' => $data], 200);
    }

    public function delete_data_except_seven_days(){
        $today = Carbon::now()->subDays(8)->format('Y-m-d');

        $playMasters = DB::select("select * from play_masters where date(created_at) <= ?",[$today]);

        foreach ($playMasters as $playMaster){
            $playDetails = PlayDetails::wherePlayMasterId($playMaster->id)->get();
            foreach ($playDetails as $playDetail){
                $playDetail->delete();
            }
            $playMaster1 = PlayMaster::find($playMaster->id);

            $playMaster1->delete();
        }

        $resultMasters = DB::select("select * from result_masters where date(created_at) <= ?",[$today]);

        foreach ($resultMasters as $resultMaster){

            $resultDetails = ResultDetail::whereResultMasterId($resultMaster->id)->get();
            foreach ($resultDetails as $resultDetail){
                $resultDetail->delete();
            }

            $resultMaster1 = ResultMaster::find($resultMaster->id);
            $resultMaster1->delete();

        }

        return response()->json(['success'=>1, 'message' => $today], 200);
    }


//    public function createResult(){
//
//        $nextGameDrawObj = NextGameDraw::first();
//        $nextDrawId = $nextGameDrawObj->next_draw_id;
//        $lastDrawId = $nextGameDrawObj->last_draw_id;
//
//        DrawMaster::query()->update(['active' => 0]);
//        if(!empty($nextGameDrawObj)){
//            DrawMaster::findOrFail($nextDrawId)->update(['active' => 1]);
//        }
//
//
//        $resultMasterController = new ResultMasterController();
//        $jsonData = $resultMasterController->save_auto_result($lastDrawId);
//
//        $resultCreatedObj = json_decode($jsonData->content(),true);
//
//        if( !empty($resultCreatedObj) && $resultCreatedObj['success']==1){
//
//            $totalDraw = DrawMaster::count();
//            if($nextDrawId==$totalDraw){
//                $nextDrawId = 1;
//            }
//            else {
//                $nextDrawId = $nextDrawId + 1;
//            }
//
//            if($lastDrawId==$totalDraw){
//                $lastDrawId = 1;
//            }
//            else{
//                $lastDrawId = $lastDrawId + 1;
//            }
//
//            $nextGameDrawObj->next_draw_id = $nextDrawId;
//            $nextGameDrawObj->last_draw_id = $lastDrawId;
//            $nextGameDrawObj->save();
//
//            return response()->json(['success'=>1, 'message' => 'Result added'], 200);
//        }else{
//            return response()->json(['success'=>0, 'message' => 'Result not added'], 401);
//        }
//
//    }

}
