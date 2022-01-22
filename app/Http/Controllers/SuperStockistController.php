<?php

namespace App\Http\Controllers;

use App\Models\CustomVoucher;
use App\Models\SuperStockist;
use App\Http\Requests\StoreSuperStockistRequest;
use App\Http\Requests\UpdateSuperStockistRequest;
use App\Models\User;
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
//            $customVoucher=CustomVoucher::where('voucher_name','=',"super stockist")->where('accounting_year',"=",2021)->first();
//            if($customVoucher) {
//                //already exist
//                $customVoucher->last_counter = $customVoucher->last_counter + 1;
//                $customVoucher->save();
//            }else{
//                //fresh entry
//                $customVoucher= new CustomVoucher();
//                $customVoucher->voucher_name="super stockist";
//                $customVoucher->accounting_year= 2021;
//                $customVoucher->last_counter=1;
//                $customVoucher->delimiter='-';
//                $customVoucher->prefix='SS';
//                $customVoucher->save();
//            }
//            //adding Zeros before number
//            $counter = str_pad($customVoucher->last_counter,4,"0",STR_PAD_LEFT);
//            //creating stockist user_id
//            $user_id = $customVoucher->prefix.$counter;

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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
