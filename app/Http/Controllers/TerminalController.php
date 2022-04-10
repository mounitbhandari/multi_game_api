<?php

namespace App\Http\Controllers;
use App\Models\UserRelationWithOther;
use App\Models\UserType;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\RechargeToUser;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use App\Models\CustomVoucher;

use App\Http\Controllers\Controller;
use App\Http\Resources\TerminalResource;
use App\Models\StockistToTerminal;
use Illuminate\Http\Request;
/////// for log
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class TerminalController extends Controller
{
    public function get_all_terminals(){
//        $terminals = UserType::find(5)->users;
//        $terminals = User::select()->whereUserTypeId(5)
//            ->join('user_relation_with_others','users.id','user_relation_with_others.terminal_id')
//            ->get();
        $terminals = DB::select("select users.id, users.visible_password , users.user_name, users.email,users.pay_out_slab_id ,users.password, users.commission ,users.remember_token, users.mobile1, users.user_type_id, users.opening_balance, users.closing_balance, users.created_by, users.inforce, user_relation_with_others.super_stockist_id, user_relation_with_others.stockist_id, user_relation_with_others.terminal_id, user_relation_with_others.changed_by, user_relation_with_others.active, user_relation_with_others.end_date, user_relation_with_others.changed_for from users
            inner join user_relation_with_others on users.id = user_relation_with_others.terminal_id
            where user_relation_with_others.active = 1");
        return TerminalResource::collection($terminals);
//        return $terminals;
    }

    // public function get_stockist_by_terminal_id(){
    //     $trminals = User::find(StockistToTerminal::whereTerminalId(14)->first()->stockist_id);
    //     return response()->json(['success'=>0, 'data' => $trminals], 500);
    // }



    public function create_terminal(Request $request){
        $requestedData = (object)$request->json()->all();

        DB::beginTransaction();
        try{

            $user = new User();
            $user->user_name = $requestedData->terminalName;
            $user->email = $requestedData->terminalName;
            $user->password = md5($requestedData->pin);
            $user->visible_password = $requestedData->pin;
            $user->user_type_id = 5;
            $user->created_by = $requestedData->createdBy;
            $user->pay_out_slab_id = $requestedData->payoutSlabId;
            $user->commission = $requestedData->commission;
            $user->opening_balance = 0;
            $user->closing_balance = 0;
            $user->save();

            $userRelation = UserRelationWithOther::whereStockistId($requestedData->stockistId)->whereTerminalId(null)->first();

            if($userRelation){
//                $userRelation->super_stockist_id = $requestedData->superStockistId;
                $userRelation->stockist_id = $requestedData->stockistId;
                $userRelation->terminal_id = $user->id;
                $userRelation->save();
            }else{
                $userRelationNew = new UserRelationWithOther();
                $userRelationNew->super_stockist_id = $requestedData->superStockistId;
                $userRelationNew->stockist_id = $requestedData->stockistId;
                $userRelationNew->terminal_id = $user->id;
                $userRelationNew->save();
            }

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }

        return response()->json(['success'=>1,'data'=> new TerminalResource($user)], 200,[],JSON_NUMERIC_CHECK);
    }


    public function update_terminal(Request $request){

        $requestedData = (object)$request->json()->all();

//        $checkValidation = UserRelationWithOther::whereSuperStockistId(5)->whereStockistId(6)->whereTerminalId(null)->first();
//
//        if(!$checkValidation){
//            return response()->json(['success'=>0,'data'=>$checkValidation], 200,[],JSON_NUMERIC_CHECK);
//        }
//
//        return response()->json(['success'=>1,'data'=>$checkValidation], 200,[],JSON_NUMERIC_CHECK);

        $terminalId = $requestedData->terminalId;
        $terminalName = $requestedData->terminalName;
        $stockist_id = $requestedData->stockistId;
        $super_stockist_id = $requestedData->superStockistId;

        $terminal = User::findOrFail($terminalId);
        $terminal->user_name = $terminalName;
        $terminal->email = $requestedData->pin;
        $terminal->pay_out_slab_id = $requestedData->payoutSlabId;
        $terminal->commission = $requestedData->commission;
        $terminal->save();

        $userRelation = UserRelationWithOther::whereTerminalId($terminalId)->whereActive(1)->first();
        if($stockist_id != ($userRelation->stockist_id)){
            $userRelation->changed_for = $terminalId;
            $userRelation->changed_by = $requestedData->userId;
            $userRelation->end_date = Carbon::today();
            $userRelation->active = 0;
            $userRelation->save();

//            return response()->json(['success'=>1,'data'=> $userRelation], 200,[],JSON_NUMERIC_CHECK);

            $checkValidation = UserRelationWithOther::whereSuperStockistId($userRelation->super_stockist_id)->whereStockistId($stockist_id)->whereTerminalId(null)->first();
//            return response()->json(['success'=>1,'data'=> $checkValidation], 200,[],JSON_NUMERIC_CHECK);

            if(!$checkValidation){
                $userRelationNull = new UserRelationWithOther();
                $userRelationNull->super_stockist_id = $userRelation->super_stockist_id;
                $userRelationNull->stockist_id = $userRelation->stockist_id;
                $userRelationNull->save();
            }

            $userRelationCreate = new UserRelationWithOther();
            $userRelationCreate->super_stockist_id = $super_stockist_id;
            $userRelationCreate->stockist_id = $stockist_id;
            $userRelationCreate->terminal_id = $terminalId;
            $userRelationCreate->save();
        }

        return response()->json(['success'=>1,'data'=> new TerminalResource($terminal)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_balance_to_terminal(Request $request){
        $requestedData = (object)$request->json()->all();

    // Validation for terminal
       $rules = array(
           'beneficiaryUid'=> ['required',
               function($attribute, $value, $fail){
                   $terminal=User::where('id', $value)->where('user_type_id','=',5)->first();
                   if(!$terminal){
                       return $fail($value.' is not a valid terminal id');
                   }
               }],
       );
       $messages = array(
           'beneficiaryUid.required' => "Terminal required"
       );

       $validator = Validator::make($request->all(),$rules,$messages);
       if ($validator->fails()) {
        return response()->json(['success'=>0, 'data' => $messages], 500);
    }

        DB::beginTransaction();
        try{

            $beneficiaryUid = $requestedData->beneficiaryUid;
            $amount = $requestedData->amount;
            $stockistId = $requestedData->stockistId;

            $beneficiaryObj = User::find($beneficiaryUid);
            $old_amount = $beneficiaryObj->closing_balance;
            $beneficiaryObj->closing_balance = $beneficiaryObj->closing_balance + $amount;
            $beneficiaryObj->save();
            $new_amount = $beneficiaryObj->closing_balance;

            $stockist = User::findOrFail($stockistId);
            $stockist->closing_balance = $stockist->closing_balance - $amount;
            $stockist->save();

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
        return response()->json(['success'=>1,'data'=> new TerminalResource($beneficiaryObj)], 200,[],JSON_NUMERIC_CHECK);

    }

    public function reset_terminal_password(Request $request){
        $requestedData = (object)$request->json()->all();
        $terminalId = $requestedData->terminalId;
        $terminalPassword = $requestedData->terminalNewPassword;
        $terminal = User::find($terminalId);
        $terminal->password = md5($terminalPassword);
        $terminal->save();
        return response()->json(['success'=>1,'data'=>$terminal], 200,[],JSON_NUMERIC_CHECK);
    }

}
