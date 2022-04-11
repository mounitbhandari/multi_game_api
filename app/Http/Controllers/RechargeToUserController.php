<?php

namespace App\Http\Controllers;

use App\Http\Resources\RechargeToUserResource;
use App\Models\RechargeToUser;
use Illuminate\Http\Request;

class RechargeToUserController extends Controller
{
    public function getTransactionByUser($id){
        $data = RechargeToUser::select()->whereRechargeDoneByUid($id)->get();
        return response()->json(['success'=>1,'data'=> RechargeToUserResource::collection($data)], 200);
    }

}
