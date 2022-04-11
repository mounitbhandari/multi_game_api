<?php

namespace App\Http\Controllers;

use App\Http\Resources\RechargeToUserResource;
use App\Models\RechargeToUser;
use Illuminate\Http\Request;

class RechargeToUserController extends Controller
{
    public function getTransactionByUser(Request $request){
        $requestedData = (object)$request->json()->all();
        $data = RechargeToUser::select()
            ->whereRechargeDoneByUid($requestedData->rechargedByID)
            ->whereBeneficiaryUid($requestedData->rechargedToID)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json(['success'=>1,'data'=> RechargeToUserResource::collection($data)], 200);
    }

}
