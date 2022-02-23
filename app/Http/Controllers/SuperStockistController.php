<?php

namespace App\Http\Controllers;

use App\Http\Resources\SuperStockistResource;
use App\Models\CustomVoucher;
use App\Models\RechargeToUser;
use App\Models\SuperStockist;
use App\Http\Requests\StoreSuperStockistRequest;
use App\Http\Requests\UpdateSuperStockistRequest;
use App\Models\User;
use App\Models\UserRelationWithOther;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SuperStockistController extends Controller
{

    public function create_super_stockist(Request $request)
    {

        $requestedData = (object)$request->json()->all();

        DB::beginTransaction();
        try{

            $user = new User();
            $user->user_name = $requestedData->userName;
            $user->email = $requestedData->pin;
            $user->password = md5($requestedData->pin);
            $user->user_type_id = 3;
            $user->created_by = $requestedData->createdBy;
            $user->commission = $requestedData->commission;
            $user->pay_out_slab_id = 1;
            $user->opening_balance = 0;
            $user->closing_balance = 0;
            $user->save();

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }

        return response()->json(['success'=>1,'data'=> new SuperStockistResource($user)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function get_super_stockist()
    {
        $data = UserType::find(3)->users;
//        return SuperStockistResource::collection($data);
        return response()->json(['success'=>1,'data'=>SuperStockistResource::collection($data)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_balance_to_super_stockist(Request $request){
        $requestedData = (object)$request->json()->all();
        $rules = array(
            'beneficiaryUid'=> ['required',
                function($attribute, $value, $fail){
                    $stockist=User::where('id', $value)->where('user_type_id','=',3)->first();
                    if(!$stockist){
                        return $fail($value.' is not a valid super stockist id');
                    }
                }],
        );
        $messages = array(
            'beneficiaryUid.required' => "Super Stockist required"
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
            $beneficiaryObj = User::find($beneficiaryUid);
            $beneficiaryObj->closing_balance = $beneficiaryObj->closing_balance + $amount;
            $beneficiaryObj->save();

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
        return response()->json(['success'=>1,'data'=> new SuperStockistResource($beneficiaryObj)], 200,[],JSON_NUMERIC_CHECK);
    }

    public function update_super_stockist(Request $request){

        $requestedData = (object)$request->json()->all();

        $user = User::find($requestedData->id);
        $user->user_name = $requestedData->userName;
        $user->commission = $requestedData->commission;
        $user->save();


        return response()->json(['success'=>1,'data'=> $user], 200,[],JSON_NUMERIC_CHECK);
    }

    public function getSuperStockistByStockist(Request $request)
    {
        $requestedData = (object)$request->json()->all();
        $data = UserRelationWithOther::whereStockistId($requestedData->stockistId)->first();
        return response()->json(['success'=>1,'data'=> $data], 200,[],JSON_NUMERIC_CHECK);
    }

    public function getStockistBySuperStockistId($id)
    {
        $data = DB::select("select distinct users.id,user_relation_with_others.super_stockist_id, user_relation_with_others.stockist_id, users.user_name from user_relation_with_others
            inner join users on user_relation_with_others.stockist_id = users.id
            where user_relation_with_others.super_stockist_id = ".$id);
        return response()->json(['success'=>1,'data'=> $data], 200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SuperStockist  $superStockist
     * @return \Illuminate\Http\Response
     */
    public function edit(SuperStockist $superStockist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSuperStockistRequest  $request
     * @param  \App\Models\SuperStockist  $superStockist
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSuperStockistRequest $request, SuperStockist $superStockist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SuperStockist  $superStockist
     * @return \Illuminate\Http\Response
     */
    public function destroy(SuperStockist $superStockist)
    {
        //
    }
}
