<?php

namespace App\Http\Controllers;

use App\Models\DrawMaster;
use App\Models\Game;
use App\Http\Controllers\Controller;
use App\Models\PlayDetails;
use App\Models\PlayMaster;
use App\Models\User;
use App\Models\UserRelationWithOther;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class GameController extends Controller
{
    public function getGame()
    {
        $game= Game::get();

        return response()->json(['success'=>1,'data'=> $game], 200,[],JSON_NUMERIC_CHECK);
    }

    public function getGameWithTime()
    {
        $today = Carbon::today()->format('Y-m-d');

        $games = Game::get();
        $commonFunctionController = new CommonFunctionController();
        $serverTime = $commonFunctionController->getServerTime();
        $x = [];
        $temp_arr = [
//            'current_time' => $serverTime
        ];

        foreach ($games as $game){
            $x = [
              'game_id' =>   $game->id,
              'game_name' =>   $game->game_name,
              'draw_id' =>   DrawMaster::whereGameId($game->id)->whereActive(1)->first()->id,
              'draw_time' =>   DrawMaster::whereGameId($game->id)->whereActive(1)->first()->visible_time,
            ];
            array_push($temp_arr, $x);
        }



        return response()->json(['success'=>1,'data'=> $temp_arr, 'current_time' => $serverTime, 'today_date' => $today], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_auto_generate($id)
    {
        $game= Game::find($id);
        $game->auto_generate= $game->auto_generate=='yes'?'no':'yes';
        $game->update();
        return response()->json(['success'=>1,'data'=> $game], 200,[],JSON_NUMERIC_CHECK);

    }

    public function activate_game($id)
    {
        $game= Game::find($id);
        $game->active= $game->active=='yes'?'no':'yes';
        $game->update();
        return response()->json(['success'=>1,'data'=> $game], 200,[],JSON_NUMERIC_CHECK);
    }

    public function get_game_total_sale_today_stockist($id){
        $terminals = UserRelationWithOther::whereStockistId($id)->whereActive(1)->get();


        $today = Carbon::today()->format('Y-m-d');

        $returnArray = [];
        $triplePrize = 0;
        $twelveCardPrize = 0;
        $sixteenCardPrize = 0;
        $singleNumberPrize = 0;
        $doubleNumberPrize = 0;

        $online_count = 0;

        //variable fol every game
        $totalTripleNumber = 0;
        $twelveCardValue = 0;
        $sixteenCardValue = 0;
        $singleNUmberValue = 0;
        $totalDoubleNumber = 0;


        $CPanelReportController = new CPanelReportController();

        foreach ($terminals as $terminal){

            $onlineCheck = PersonalAccessToken::whereTokenableId($terminal->terminal_id)->whereRaw('date(created_at) = ?', [$today])->first();
            if($onlineCheck){
                $online_count = $online_count + 1;
            }

            $tripleAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(1)->get();
            $twelveCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(2)->get();
            $sixteenCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(3)->get();
            $singleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(4)->get();
            $doubleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(5)->get();


            foreach ($tripleAllPlayMasters as $tripleAllPlayMaster){
                $triplePrize = $triplePrize + $CPanelReportController->get_prize_value_by_barcode($tripleAllPlayMaster->id);
            }

            foreach ($twelveCardAllPlayMasters as $twelveCardAllPlayMaster){
                $twelveCardPrize = $twelveCardPrize + $CPanelReportController->get_prize_value_by_barcode($twelveCardAllPlayMaster->id);
            }

            foreach ($sixteenCardAllPlayMasters as $sixteenCardAllPlayMaster){
                $sixteenCardPrize = $sixteenCardPrize + $CPanelReportController->get_prize_value_by_barcode($sixteenCardAllPlayMaster->id);
            }

            foreach ($singleNumberAllPlayMasters as $singleNumberAllPlayMaster){
                $singleNumberPrize = $singleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($singleNumberAllPlayMaster->id);
            }

            foreach ($doubleNumberAllPlayMasters as $doubleNumberAllPlayMaster){
                $doubleNumberPrize = $doubleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($doubleNumberAllPlayMaster->id);
            }


            $singleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 1 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($singleNumber)){
                $singleNumber = $singleNumber[0]->single_number;
            }else{
                $singleNumber = 0;
            }

            $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 5 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($doubleNumber)){
                $doubleNumber = $doubleNumber[0]->double_number;
            }else{
                $doubleNumber = 0;
            }

            $tripleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as triple_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 2 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($tripleNumber)){
                $tripleNumber = $tripleNumber[0]->triple_number;
            }else{
                $tripleNumber = 0;
            }

            $totalTripleNumber = $totalTripleNumber + ($singleNumber + $doubleNumber + $tripleNumber);

            $twelveCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as twelve_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 3 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($twelveCard)){
                $twelveCardValue = $twelveCardValue + $twelveCard[0]->twelve_card;
            }else{
                $twelveCardValue = $twelveCardValue + 0;
            }

            $sixteenCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as sixteen_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 4 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($sixteenCard)){
                $sixteenCardValue = $sixteenCardValue + $sixteenCard[0]->sixteen_card;
            }else{
                $sixteenCardValue = $sixteenCardValue + 0;
            }

            $singleNUmber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 6 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($singleNUmber)){
                $singleNUmberValue = $singleNUmberValue + $singleNUmber[0]->single_number;
            }else{
                $singleNUmberValue = $singleNUmberValue + 0;
            }

            $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 7 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($doubleNumber)){
                $doubleNumber = $doubleNumber[0]->double_number;
            }else{
                $doubleNumber =  0;
            }

            $andarNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as andar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 8 and play_masters.user_id = ?
            group by game_types.mrp",[$today , $terminal->terminal_id]);

            if(!empty($andarNumber)){
                $andarNumber = $andarNumber[0]->andar_number;
            }else{
                $andarNumber = 0;
            }

            $baharNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as bahar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 9
            group by game_types.mrp",[$today]);

            if(!empty($baharNumber)){
                $baharNumber = $baharNumber[0]->bahar_number;
            }else{
                $baharNumber = 0;
            }

            $totalDoubleNumber = $totalDoubleNumber + ($doubleNumber + $andarNumber + $baharNumber);
        }

        $x = [
            'game_name' => 'Triple Chance',
            'total_bet' =>   $totalTripleNumber,
            'total_win' =>   $triplePrize,
            'profit' =>   $totalTripleNumber - $triplePrize
        ];
        array_push($returnArray , $x);

        $x = [
            'game_name' => '12 Card',
            'total_bet' =>  (int)$twelveCardValue,
            'total_win' =>   $twelveCardPrize,
            'profit' =>   (int)$twelveCardValue - $twelveCardPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => '16 Card',
            'total_bet' =>  (int)$sixteenCardValue,
            'total_win' =>   $sixteenCardPrize,
            'profit' =>   (int)$sixteenCardValue - $sixteenCardPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => 'Single Number',
            'total_bet' =>  (int)$singleNUmberValue,
            'total_win' =>   $singleNumberPrize,
            'profit' =>   (int)$singleNUmberValue - $singleNumberPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => 'Double Number',
            'total_bet' =>  (int)$totalDoubleNumber,
            'total_win' =>   $doubleNumberPrize,
            'profit' =>   (int)$totalDoubleNumber - $doubleNumberPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'online' => $online_count
        ];
        array_push($returnArray , $x);

        return response()->json(['success'=>1,'data'=> $returnArray], 200);

    }

    public function stockist_turnover_report(Request $request){
        $requestedData = (object)($request->json()->all());
        $terminals = UserRelationWithOther::whereStockistId($requestedData->stockist_id)->whereActive(1)->get();

        $returnArray = [];
        $totalPrizeClaimed = 0;
        $totalPrizeUnclaimed = 0;
        $totalCommission = 0;

        $totalBet = 0;

        $CPanelReportController = new CPanelReportController();

        foreach ($terminals as $terminal) {
            $allPlayMasters = DB::select("select * from play_masters where date(created_at) >= ? and DATE(created_at) <= ? and user_id = ? and game_id = ?",
                [$requestedData->start_date,$requestedData->end_date, $terminal->terminal_id, $requestedData->game_id]);

            foreach ($allPlayMasters as $allPlayMaster) {
                $totalPrizeClaimed = $allPlayMaster->is_claimed == 1 ? ($totalPrizeClaimed + $CPanelReportController->get_prize_value_by_barcode($allPlayMaster->id)): $totalPrizeClaimed + 0;
                $totalPrizeUnclaimed = $allPlayMaster->is_claimed == 0 ? ($totalPrizeUnclaimed + $CPanelReportController->get_prize_value_by_barcode($allPlayMaster->id)): $totalPrizeUnclaimed + 0;
                $totalBet = $totalBet + $CPanelReportController->total_sale_by_play_master_id($allPlayMaster->id);
//                $totalCommission = $totalCommission + ($totalBet * (floor((PlayDetails::wherePlayMasterId($allPlayMaster->id)->first())->commission)/100));
                $tempCommission = DB::select("select ifnull(commission,0)/100 as commission from play_details where play_master_id = ?",[$allPlayMaster->id]);
                $totalCommission = $totalCommission + ($totalBet * ($tempCommission? $tempCommission[0]->commission : 0));
            }
        }

        $x = [
            'total_bet' =>   $totalBet,
            'total_win_claimed' =>   $totalPrizeClaimed,
            'total_win_unclaimed' =>   $totalPrizeUnclaimed,
            'profit' =>   $totalBet - $totalPrizeClaimed,
            'total_commission' =>  sprintf('%0.2f', $totalCommission),
        ];
        array_push($returnArray , $x);

        return response()->json(['success'=>1,'data'=> $returnArray[0]], 200);
    }

    public function super_stockist_turnover_report(Request $request){
        $requestedData = (object)($request->json()->all());
        $terminals = UserRelationWithOther::whereSuperStockistId($requestedData->super_stockist_id)->whereActive(1)->get();

        $returnArray = [];
        $totalPrizeClaimed = 0;
        $totalPrizeUnclaimed = 0;
        $totalCommission = 0;

        $totalBet = 0;

        $CPanelReportController = new CPanelReportController();

        foreach ($terminals as $terminal) {
            $allPlayMasters = DB::select("select * from play_masters where date(created_at) >= ? and DATE(created_at) <= ? and user_id = ? and game_id = ?",
                [$requestedData->start_date,$requestedData->end_date, $terminal->terminal_id, $requestedData->game_id]);

            foreach ($allPlayMasters as $allPlayMaster) {
                $totalPrizeClaimed = $allPlayMaster->is_claimed == 1 ? ($totalPrizeClaimed + $CPanelReportController->get_prize_value_by_barcode($allPlayMaster->id)): $totalPrizeClaimed + 0;
                $totalPrizeUnclaimed = $allPlayMaster->is_claimed == 0 ? ($totalPrizeUnclaimed + $CPanelReportController->get_prize_value_by_barcode($allPlayMaster->id)): $totalPrizeUnclaimed + 0;
                $totalBet = $totalBet + $CPanelReportController->total_sale_by_play_master_id($allPlayMaster->id);
//                $totalCommission = $totalCommission + ($totalBet * (floor((PlayDetails::wherePlayMasterId($allPlayMaster->id)->first())->commission)/100));
                $tempCommission = DB::select("select ifnull(commission,0)/100 as commission from play_details where play_master_id = ?",[$allPlayMaster->id]);
                $totalCommission = $totalCommission + ($totalBet * ($tempCommission? $tempCommission[0]->commission : 0));
            }
        }

        $x = [
            'total_bet' =>   $totalBet,
            'total_win_claimed' =>   $totalPrizeClaimed,
            'total_win_unclaimed' =>   $totalPrizeUnclaimed,
            'profit' =>   $totalBet - $totalPrizeClaimed,
            'total_commission' =>   sprintf('%0.2f', $totalCommission),
        ];
        array_push($returnArray , $x);

        // return response()->json(['success'=>1,'data'=> $returnArray[0]], 200);
        return response()->json(['success'=>1,'data'=> $returnArray], 200);
    }

    public function get_game_total_sale_today_super_stockist($id){
        $terminals = UserRelationWithOther::whereSuperStockistId($id)->whereActive(1)->get();


        $today = Carbon::today()->format('Y-m-d');

        $returnArray = [];
        $triplePrize = 0;
        $twelveCardPrize = 0;
        $sixteenCardPrize = 0;
        $singleNumberPrize = 0;
        $doubleNumberPrize = 0;

        $online_count = 0;

        //variable fol every game
        $totalTripleNumber = 0;
        $twelveCardValue = 0;
        $sixteenCardValue = 0;
        $singleNUmberValue = 0;
        $totalDoubleNumber = 0;


        $CPanelReportController = new CPanelReportController();

        foreach ($terminals as $terminal){

            $onlineCheck = PersonalAccessToken::whereTokenableId($terminal->terminal_id)->whereRaw('date(created_at) = ?', [$today])->first();
            if($onlineCheck){
                $online_count = $online_count + 1;
            }

            $tripleAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(1)->get();
            $twelveCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(2)->get();
            $sixteenCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(3)->get();
            $singleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(4)->get();
            $doubleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereUserId($terminal->terminal_id)->whereGameId(5)->get();


            foreach ($tripleAllPlayMasters as $tripleAllPlayMaster){
                $triplePrize = $triplePrize + $CPanelReportController->get_prize_value_by_barcode($tripleAllPlayMaster->id);
            }

            foreach ($twelveCardAllPlayMasters as $twelveCardAllPlayMaster){
                $twelveCardPrize = $twelveCardPrize + $CPanelReportController->get_prize_value_by_barcode($twelveCardAllPlayMaster->id);
            }

            foreach ($sixteenCardAllPlayMasters as $sixteenCardAllPlayMaster){
                $sixteenCardPrize = $sixteenCardPrize + $CPanelReportController->get_prize_value_by_barcode($sixteenCardAllPlayMaster->id);
            }

            foreach ($singleNumberAllPlayMasters as $singleNumberAllPlayMaster){
                $singleNumberPrize = $singleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($singleNumberAllPlayMaster->id);
            }

            foreach ($doubleNumberAllPlayMasters as $doubleNumberAllPlayMaster){
                $doubleNumberPrize = $doubleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($doubleNumberAllPlayMaster->id);
            }


            $singleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 1 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($singleNumber)){
                $singleNumber = $singleNumber[0]->single_number;
            }else{
                $singleNumber = 0;
            }

            $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 5 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($doubleNumber)){
                $doubleNumber = $doubleNumber[0]->double_number;
            }else{
                $doubleNumber = 0;
            }

            $tripleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as triple_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 2 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($tripleNumber)){
                $tripleNumber = $tripleNumber[0]->triple_number;
            }else{
                $tripleNumber = 0;
            }

            $totalTripleNumber = $totalTripleNumber + ($singleNumber + $doubleNumber + $tripleNumber);

            $twelveCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as twelve_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 3 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($twelveCard)){
                $twelveCardValue = $twelveCardValue + $twelveCard[0]->twelve_card;
            }else{
                $twelveCardValue = $twelveCardValue + 0;
            }

            $sixteenCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as sixteen_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 4 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($sixteenCard)){
                $sixteenCardValue = $sixteenCardValue + $sixteenCard[0]->sixteen_card;
            }else{
                $sixteenCardValue = $sixteenCardValue + 0;
            }

            $singleNUmber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 6 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($singleNUmber)){
                $singleNUmberValue = $singleNUmberValue + $singleNUmber[0]->single_number;
            }else{
                $singleNUmberValue = $singleNUmberValue + 0;
            }

            $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 7 and play_masters.user_id = ?
            group by game_types.mrp",[$today, $terminal->terminal_id]);

            if(!empty($doubleNumber)){
                $doubleNumber = $doubleNumber[0]->double_number;
            }else{
                $doubleNumber =  0;
            }

            $andarNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as andar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            inner join play_masters on play_details.play_master_id = play_masters.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 8 and play_masters.user_id = ?
            group by game_types.mrp",[$today , $terminal->terminal_id]);

            if(!empty($andarNumber)){
                $andarNumber = $andarNumber[0]->andar_number;
            }else{
                $andarNumber = 0;
            }

            $baharNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as bahar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 9
            group by game_types.mrp",[$today]);

            if(!empty($baharNumber)){
                $baharNumber = $baharNumber[0]->bahar_number;
            }else{
                $baharNumber = 0;
            }

            $totalDoubleNumber = $totalDoubleNumber + ($doubleNumber + $andarNumber + $baharNumber);
        }

        $x = [
            'game_name' => 'Triple Chance',
            'total_bet' =>   $totalTripleNumber,
            'total_win' =>   $triplePrize,
            'profit' =>   $totalTripleNumber - $triplePrize
        ];
        array_push($returnArray , $x);

        $x = [
            'game_name' => '12 Card',
            'total_bet' =>  (int)$twelveCardValue,
            'total_win' =>   $twelveCardPrize,
            'profit' =>   (int)$twelveCardValue - $twelveCardPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => '16 Card',
            'total_bet' =>  (int)$sixteenCardValue,
            'total_win' =>   $sixteenCardPrize,
            'profit' =>   (int)$sixteenCardValue - $sixteenCardPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => 'Single Number',
            'total_bet' =>  (int)$singleNUmberValue,
            'total_win' =>   $singleNumberPrize,
            'profit' =>   (int)$singleNUmberValue - $singleNumberPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'game_name' => 'Double Number',
            'total_bet' =>  (int)$totalDoubleNumber,
            'total_win' =>   $doubleNumberPrize,
            'profit' =>   (int)$totalDoubleNumber - $doubleNumberPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'online' => $online_count
        ];
        array_push($returnArray , $x);

        return response()->json(['success'=>1,'data'=> $returnArray], 200);

    }

    public function get_game_total_sale_today()
    {
        $today = Carbon::today()->format('Y-m-d');

        $returnArray = [];
        $triplePrize = 0;
        $twelveCardPrize = 0;
        $sixteenCardPrize = 0;
        $singleNumberPrize = 0;
        $doubleNumberPrize = 0;

        $CPanelReportController = new CPanelReportController();

        $tripleAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereGameId(1)->get();
        $twelveCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereGameId(2)->get();
        $sixteenCardAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereGameId(3)->get();
        $singleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereGameId(4)->get();
        $doubleNumberAllPlayMasters = PlayMaster::where(DB::raw("date(created_at)"),$today)->whereGameId(5)->get();

        $online_count = (DB::select("select count(personal_access_tokens.id) as total_count from personal_access_tokens
            inner join users on personal_access_tokens.tokenable_id = users.id
            where date(personal_access_tokens.created_at) = ? and users.user_type_id = 5",[$today]))[0]->total_count;


        foreach ($tripleAllPlayMasters as $tripleAllPlayMaster){
            $triplePrize = $triplePrize + $CPanelReportController->get_prize_value_by_barcode($tripleAllPlayMaster->id);
        }

        foreach ($twelveCardAllPlayMasters as $twelveCardAllPlayMaster){
            $twelveCardPrize = $twelveCardPrize + $CPanelReportController->get_prize_value_by_barcode($twelveCardAllPlayMaster->id);
        }

        foreach ($sixteenCardAllPlayMasters as $sixteenCardAllPlayMaster){
            $sixteenCardPrize = $sixteenCardPrize + $CPanelReportController->get_prize_value_by_barcode($sixteenCardAllPlayMaster->id);
        }

        foreach ($singleNumberAllPlayMasters as $singleNumberAllPlayMaster){
            $singleNumberPrize = $singleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($singleNumberAllPlayMaster->id);
        }

        foreach ($doubleNumberAllPlayMasters as $doubleNumberAllPlayMaster){
            $doubleNumberPrize = $doubleNumberPrize + $CPanelReportController->get_prize_value_by_barcode($doubleNumberAllPlayMaster->id);
        }

        $singleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 1
            group by game_types.mrp",[$today]);

        if(!empty($singleNumber)){
            $singleNumber = $singleNumber[0]->single_number;
        }else{
            $singleNumber = 0;
        }

        $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 5
            group by game_types.mrp",[$today]);

        if(!empty($doubleNumber)){
            $doubleNumber = $doubleNumber[0]->double_number;
        }else{
            $doubleNumber = 0;
        }

        $tripleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as triple_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 2
            group by game_types.mrp",[$today]);

        if(!empty($tripleNumber)){
            $tripleNumber = $tripleNumber[0]->triple_number;
        }else{
            $tripleNumber = 0;
        }

        $totalTripleNumber = $singleNumber + $doubleNumber + $tripleNumber;

        $x = [
          'game_name' => 'Triple Chance',
          'total_bet' =>   $totalTripleNumber,
          'total_win' =>   $triplePrize,
          'profit' =>   $totalTripleNumber - $triplePrize
        ];

        array_push($returnArray , $x);

        $twelveCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as twelve_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 3
            group by game_types.mrp",[$today]);

        if(!empty($twelveCard)){
            $twelveCard = $twelveCard[0]->twelve_card;
        }else{
            $twelveCard = 0;
        }

        $x = [
            'game_name' => '12 Card',
            'total_bet' =>  (int)$twelveCard,
            'total_win' =>   $twelveCardPrize,
            'profit' =>   (int)$twelveCard - $twelveCardPrize
        ];

        array_push($returnArray , $x);

        $sixteenCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as sixteen_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 4
            group by game_types.mrp",[$today]);

        if(!empty($sixteenCard)){
            $sixteenCard = $sixteenCard[0]->sixteen_card;
        }else{
            $sixteenCard = 0;
        }

        $x = [
            'game_name' => '16 Card',
            'total_bet' =>  (int)$sixteenCard,
            'total_win' =>   $sixteenCardPrize,
            'profit' =>   (int)$sixteenCard - $sixteenCardPrize
        ];

        array_push($returnArray , $x);


        $singleNUmber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 6
            group by game_types.mrp",[$today]);

        if(!empty($singleNUmber)){
            $singleNUmber = $singleNUmber[0]->single_number;
        }else{
            $singleNUmber = 0;
        }

        $x = [
            'game_name' => 'Single Number',
            'total_bet' =>  (int)$singleNUmber,
            'total_win' =>   $singleNumberPrize,
            'profit' =>   (int)$singleNUmber - $singleNumberPrize
        ];

        array_push($returnArray , $x);

        $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 7
            group by game_types.mrp",[$today]);

        if(!empty($doubleNumber)){
            $doubleNumber = $doubleNumber[0]->double_number;
        }else{
            $doubleNumber = 0;
        }

        $andarNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as andar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 8
            group by game_types.mrp",[$today]);

        if(!empty($andarNumber)){
            $andarNumber = $andarNumber[0]->andar_number;
        }else{
            $andarNumber = 0;
        }

        $baharNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as bahar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 9
            group by game_types.mrp",[$today]);

        if(!empty($baharNumber)){
            $baharNumber = $baharNumber[0]->bahar_number;
        }else{
            $baharNumber = 0;
        }

        $totalDoubleNumber = $doubleNumber + $andarNumber + $baharNumber;

        $x = [
            'game_name' => 'Double Number',
            'total_bet' =>  (int)$totalDoubleNumber,
            'total_win' =>   $doubleNumberPrize,
            'profit' =>   (int)$totalDoubleNumber - $doubleNumberPrize
        ];

        array_push($returnArray , $x);

        $x = [
            'online' => $online_count
        ];
        array_push($returnArray , $x);

        return response()->json(['success'=>1,'data'=> $returnArray], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function edit(Game $game)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Game $game)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Game  $game
     * @return \Illuminate\Http\Response
     */
    public function destroy(Game $game)
    {
        //
    }
}
