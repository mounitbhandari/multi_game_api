<?php

namespace App\Http\Controllers;

use App\Models\DrawMaster;
use App\Models\Game;
use App\Http\Controllers\Controller;
use App\Models\PlayMaster;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
