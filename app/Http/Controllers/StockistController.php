<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockistResource;
use App\Models\PlayMaster;
use App\Models\RechargeToUser;
use App\Models\User;
use App\Models\UserRelationWithOther;
use App\Models\UserType;
use App\Models\CustomVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockistController extends Controller
{


    public function customer_sale_reports(Request $request){
        $requestedData = (object)$request->json()->all();
        $start_date = $requestedData->startDate;
        $end_date = $requestedData->endDate;
        $userID = $requestedData->userID;

        $cPanelRepotControllerObj = new CPanelReportController();


        $data = DB::select("select table1.play_master_id, table1.terminal_pin, table1.user_name, table1.user_id, table1.stockist_id, table1.total, table1.commission, users.user_name as stokiest_name from (select max(play_master_id) as play_master_id,terminal_pin,user_name,user_id,stockist_id,
        sum(total) as total,round(sum(commission),2) as commission from (
        select max(play_masters.id) as play_master_id,users.user_name,users.email as terminal_pin,
        round(sum(play_details.quantity * play_details.mrp)) as total,
        sum(play_details.quantity * play_details.mrp)* (max(play_details.commission)/100) as commission,
        play_masters.user_id, user_relation_with_others.stockist_id
        FROM play_masters
        inner join play_details on play_details.play_master_id = play_masters.id
        inner join game_types ON game_types.id = play_details.game_type_id
        inner join users ON users.id = play_masters.user_id
        left join user_relation_with_others on play_masters.user_id = user_relation_with_others.terminal_id
        where play_masters.is_cancelled=0 and date(play_masters.created_at) >= ? and date(play_masters.created_at) <= ? and stockist_id = ?
        group by user_relation_with_others.stockist_id, play_masters.user_id,users.user_name,play_details.game_type_id,users.email) as table1 group by user_name,user_id,terminal_pin,stockist_id) as table1
        left join users on table1.stockist_id = users.id ",[$start_date,$end_date,$userID]);

//        $newData = PlayMaster::where('user_id', $data[0]->user_id)->get();

//        foreach($data as $x) {
//            $newPrize = 0;
//            $tempntp = 0;
//            $newData = PlayMaster::where('user_id', $x->user_id)->get();
//            foreach ($newData as $y) {
//                $tempData = 0;
////                $newPrize += $this->get_prize_value_by_barcode($y->id);
//                $tempPrize = $cPanelRepotControllerObj->get_prize_value_by_barcode($y->id);
//                if ($tempPrize > 0 && $y->is_claimed == 1) {
//                    $newPrize += $cPanelRepotControllerObj->get_prize_value_by_barcode($y->id);
//                } else {
//                    $newPrize += 0;
//                }
//                $detail = (object)$x;
//                $detail->prize_value = $newPrize;
//            }
//        }

        foreach($data as $x){
            $newPrize = 0;
            $tempPrize = 0;
            $newData = PlayMaster::where('user_id',$x->user_id)->get();
            foreach($newData as $y) {
                $tempPrize += $cPanelRepotControllerObj->get_prize_value_by_barcode($y->id);
                if ($tempPrize > 0 && $y->is_claimed == 1) {
                    $newPrize += $cPanelRepotControllerObj->get_prize_value_by_barcode($y->id);
                } else {
                    $newPrize += 0;
                }
            }
            $detail = (object)$x;
            $detail->prize_value = $newPrize;
        }

        return response()->json(['success'=> 1, 'data' => $data], 200);
//        return response()->json(['success'=> 1, 'data' => $newData], 200);
    }


    public function barcode_wise_report_by_date(Request $request){
        $requestedData = (object)$request->json()->all();
        $start_date = $requestedData->startDate;
        $end_date = $requestedData->endDate;
        $userID = $requestedData->userID;

        $cPanelRepotControllerObj = new CPanelReportController();

        $data = PlayMaster::select('play_masters.id as play_master_id', DB::raw('substr(play_masters.barcode_number, 1, 8) as barcode_number')
            ,'draw_masters.visible_time as draw_time',
            'users.email as terminal_pin','play_masters.created_at as ticket_taken_time','games.game_name','play_masters.is_claimed', 'games.id as game_id'
        )
            ->join('draw_masters','play_masters.draw_master_id','draw_masters.id')
            ->join('users','users.id','play_masters.user_id')
            ->join('play_details','play_details.play_master_id','play_masters.id')
            ->join('game_types','game_types.id','play_details.game_type_id')
            ->join('games','games.id','game_types.game_id')
            ->join('user_relation_with_others','user_relation_with_others.terminal_id','play_masters.user_id')
            ->where('play_masters.is_cancelled',0)
            ->where('user_relation_with_others.stockist_id',$userID)
            ->whereRaw('date(play_masters.created_at) >= ?', [$start_date])
            ->whereRaw('date(play_masters.created_at) <= ?', [$end_date])
            ->groupBy('play_masters.id','play_masters.barcode_number','draw_masters.visible_time','users.email','play_masters.created_at','games.game_name','play_masters.is_claimed', 'games.id')
            ->orderBy('play_masters.created_at','desc')
            ->get();

        foreach($data as $x){
            $detail = (object)$x;
            $detail->total_quantity = $cPanelRepotControllerObj->get_total_quantity_by_barcode($detail->play_master_id);
            $detail->prize_value = $cPanelRepotControllerObj->get_prize_value_by_barcode($detail->play_master_id);
            $detail->amount = $cPanelRepotControllerObj->get_total_amount_by_barcode($detail->play_master_id);
        }

        return response()->json(['success'=> 1, 'data' => $data], 200);
    }

    public function get_all_stockists(){

        $stockists = UserType::find(4)->users;
//        return response()->json(['success'=> 1, 'data' => $stockists], 200);
        return StockistResource::collection($stockists);
    }

    public function get_stockist($id){

        $stockists = User::select()
            ->join('user_types','user_types.id','users.user_type_id')
            ->where('users.id',$id)
            ->where('user_type_id',3)
            ->first();
        return StockistResource::collection($stockists);
    }

    public function create_stockist(Request $request){
        $requestedData = (object)$request->json()->all();

        DB::beginTransaction();
        try{

            $user = new User();
            $user->user_name = $requestedData->userName;
            $user->email = $requestedData->userName;
            $user->password = md5($requestedData->pin);
            $user->visible_password = $requestedData->pin;
            $user->user_type_id = 4;
            $user->pay_out_slab_id = 1;
            $user->created_by = $requestedData->createdBy;
            $user->commission = $requestedData->commission;
            $user->opening_balance = 0;
            $user->closing_balance = 0;
            $user->save();

            $userRelation = new UserRelationWithOther();
            $userRelation->super_stockist_id = $requestedData->superStockistId;
            $userRelation->stockist_id = $user->id;
            $userRelation->save();

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }

        return response()->json(['success'=>1,'data'=> new StockistResource($user)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_stockist(Request $request){
        $requestedData = (object)$request->json()->all();

        $stockist_id = $requestedData->stockistId;
        $super_stockist_id = $requestedData->superStockistId;
        $stockist_name = $requestedData->stockistName;

        $stockist = User::findOrFail($stockist_id);
        $stockist->user_name = $stockist_name;
        $stockist->commission = $requestedData->commission;
        $stockist->save();

        $userRelation = UserRelationWithOther::whereStockistId($stockist_id)->whereActive(1)->first();

        if($userRelation->super_stockist_id != $super_stockist_id){
            $userRelations = UserRelationWithOther::whereStockistId($stockist_id)->whereActive(1)->get();

            foreach ($userRelations as $user){
                $userRelation = UserRelationWithOther::whereId($user->id)->first();
                $userRelation->changed_for = $stockist_id;
                $userRelation->changed_by = $requestedData->userId;
                $userRelation->end_date = Carbon::today();
                $userRelation->active = 0;
                $userRelation->save();

                $userRelationSave = new UserRelationWithOther();
                $userRelationSave->super_stockist_id = $super_stockist_id;
                $userRelationSave->stockist_id = $stockist_id;
                $userRelationSave->terminal_id = $user->terminal_id;
                $userRelationSave->save();
            }
        }

        return response()->json(['success'=>1,'data'=> new StockistResource($stockist)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_balance_to_stockist(Request $request){
//        $requestedData = (object)$request->json()->all();
//
//        if(isset($requestedData->superStockiestID)){
//            $superStockiest = $requestedData->superStockiestID;
//        }else{
//            $superStockiest = $requestedData->rechargeDoneByUid;
//        }
//
//        return response()->json(['success'=>1,'data'=> $superStockiest], 200,[],JSON_NUMERIC_CHECK);

        $rules = array(
            'beneficiaryUid'=> ['required',
                function($attribute, $value, $fail){
                    $stockist=User::where('id', $value)->where('user_type_id','=',4)->first();
                    if(!$stockist){
                        return $fail($value.' is not a valid stockist id');
                    }
                }],
        );
        $messages = array(
            'beneficiaryUid.required' => "Stockist required"
        );

        $validator = Validator::make($request->all(),$rules,$messages);
        if ($validator->fails()) {
            return response()->json(['success'=>0, 'data' => $messages], 500);
        }


        DB::beginTransaction();
        try{
            $requestedData = (object)$request->json()->all();
            $beneficiaryUid = $requestedData->beneficiaryUid;
            $amount = $requestedData->amount;

            if(isset($requestedData->superStockiestID)){
                $superStockiest = $requestedData->superStockiestID;
            }else{
                $superStockiest = $requestedData->rechargeDoneByUid;
            }

            $beneficiaryObj = User::find($beneficiaryUid);
            $old_amount = $beneficiaryObj->closing_balance;
            $beneficiaryObj->closing_balance = $beneficiaryObj->closing_balance + $amount;
            $beneficiaryObj->save();
            $new_amount = $beneficiaryObj->closing_balance;

            $user = User::findOrFail($superStockiest);
            $user->closing_balance = $user->closing_balance - $amount;
            $user->save();

            $rechargeToUser = new RechargeToUser();
            $rechargeToUser->beneficiary_uid = $requestedData->beneficiaryUid;
            $rechargeToUser->recharge_done_by_uid = $requestedData->rechargeDoneByUid;
            $rechargeToUser->old_amount = $old_amount;
            $rechargeToUser->amount = $requestedData->amount;
            $rechargeToUser->new_amount = $new_amount;
            $rechargeToUser->save();
            DB::commit();

        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }
        return response()->json(['success'=>1,'data'=> new StockistResource($beneficiaryObj)], 200,[],JSON_NUMERIC_CHECK);

    }

}
