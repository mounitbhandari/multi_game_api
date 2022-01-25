<?php

namespace App\Http\Controllers;

use App\Models\CustomVoucher;
use App\Models\SuperStockist;
use App\Http\Requests\StoreSuperStockistRequest;
use App\Http\Requests\UpdateSuperStockistRequest;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

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
            $user->opening_balance = 0;
            $user->closing_balance = 0;
            $user->save();

            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json(['success'=>0, 'data' => null, 'error'=>$e->getMessage()], 500);
        }

        return response()->json(['success'=>1,'data'=> $user], 200,[],JSON_NUMERIC_CHECK);
    }

    public function get_super_stockist()
    {
        $data = UserType::find(3)->users;
        return response()->json(['success'=>1,'data'=> $data], 200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSuperStockistRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSuperStockistRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SuperStockist  $superStockist
     * @return \Illuminate\Http\Response
     */
    public function show(SuperStockist $superStockist)
    {
        //
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
