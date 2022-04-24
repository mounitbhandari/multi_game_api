<?php

namespace App\Http\Controllers;

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
            where date(play_details.created_at) = ? and play_details.game_type_id = 1",[$today])[0]->single_number;

        $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 5",[$today])[0]->double_number;

        $tripleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as triple_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 2",[$today])[0]->triple_number;

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
            where date(play_details.created_at) = ? and play_details.game_type_id = 3",[$today])[0]->twelve_card;

        $x = [
            'game_name' => '12 Card',
            'total_bet' =>  (int)$twelveCard,
            'total_win' =>   $twelveCardPrize,
            'profit' =>   (int)$twelveCard - $twelveCardPrize
        ];

        array_push($returnArray , $x);

        $sixteenCard = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as sixteen_card from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 4",[$today])[0]->sixteen_card;

        $x = [
            'game_name' => '16 Card',
            'total_bet' =>  (int)$sixteenCard,
            'total_win' =>   $sixteenCardPrize,
            'profit' =>   (int)$sixteenCard - $sixteenCardPrize
        ];

        array_push($returnArray , $x);


        $singleNUmber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as single_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 6",[$today])[0]->single_number;

        $x = [
            'game_name' => 'Single Number',
            'total_bet' =>  (int)$singleNUmber,
            'total_win' =>   $singleNumberPrize,
            'profit' =>   (int)$singleNUmber - $singleNumberPrize
        ];

        array_push($returnArray , $x);

        $doubleNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as double_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 7",[$today])[0]->double_number;

        $andarNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as andar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 8",[$today])[0]->andar_number;

        $baharNumber = DB::select("select ifnull(ifnull(sum(play_details.quantity),0) * game_types.mrp,0) as bahar_number from play_details
            inner join game_types on play_details.game_type_id = game_types.id
            where date(play_details.created_at) = ? and play_details.game_type_id = 9",[$today])[0]->bahar_number;

        $totalDoubleNumber = $doubleNumber + $andarNumber + $baharNumber;

        $x = [
            'game_name' => 'Double Number',
            'total_bet' =>  (int)$totalDoubleNumber,
            'total_win' =>   $doubleNumberPrize,
            'profit' =>   (int)$totalDoubleNumber - $doubleNumberPrize
        ];

        array_push($returnArray , $x);
        
        return response()->json(['success'=>$triplePrize,'data'=> $returnArray], 200);
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
