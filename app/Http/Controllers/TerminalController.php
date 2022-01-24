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
        $terminals = UserType::find(4)->users;
        return TerminalResource::collection($terminals);
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
            $user->email = $requestedData->pin;
            $user->password = md5($requestedData->pin);
            $user->user_type_id = 5;
            $user->created_by = $requestedData->createdBy;
            $user->opening_balance = 0;
            $user->closing_balance = 0;
            $user->save();

            $userRelation = UserRelationWithOther::whereStockistId($requestedData->stockistId)->whereTerminalId(null)->first();

            if($userRelation){
                $userRelation->super_stockist_id = $requestedData->superStockistId;
                $userRelation->stockist_id = $requestedData->stockistId;
                $userRelation->terminal_id = $user->id;
                $userRelation->save();
            }else{
                $userRelation = new UserRelationWithOther();
                $userRelation->super_stockist_id = $requestedData->superStockistId;
                $userRelation->stockist_id = $requestedData->stockistId;
                $userRelation->terminal_id = $user->id;
                $userRelation->save();
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

        $terminalId = $requestedData->terminalId;
        $terminalName = $requestedData->terminalName;
        $stockist_id = $requestedData->stockistId;

        $terminal = User::findOrFail($terminalId);
        $terminal->user_name = $terminalName;
        $terminal->save();

        $userRelation = UserRelationWithOther::whereTerminalId($terminalId)->whereActive(1)->first();
        if($stockist_id != ($userRelation->stockist_id)){
            $userRelation->changed_for = $terminalId;
            $userRelation->changed_by = $requestedData->userId;
            $userRelation->end_date = Carbon::today();
            $userRelation->active = 0;
            $userRelation->save();

            $userRelationCreate = new UserRelationWithOther();
            $userRelationCreate->super_stockist_id = $userRelation->super_stockist_id;
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
                   $terminal=User::where('id', $value)->where('user_type_id','=',4)->first();
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
            $beneficiaryObj->closing_balance = $beneficiaryObj->closing_balance + $amount;
            $beneficiaryObj->save();

            $stockist = User::findOrFail($stockistId);
            $stockist->closing_balance = $stockist->closing_balance - $amount;
            $stockist->save();

            $rechargeToUser = new RechargeToUser();
            $rechargeToUser->beneficiary_uid = $requestedData->beneficiaryUid;
            $rechargeToUser->recharge_done_by_uid = $requestedData->rechargeDoneByUid;
            $rechargeToUser->amount = $requestedData->amount;
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
