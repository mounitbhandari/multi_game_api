<?php

namespace App\Http\Controllers;

use App\Models\DoubleNumberCombination;
use App\Http\Requests\StoreDoubleNumberCombinationRequest;
use App\Http\Requests\UpdateDoubleNumberCombinationRequest;
use App\Http\Resources\DoubleNumberCombinationResource;
use PhpParser\Node\Expr\Cast\Double;

class DoubleNumberCombinationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_all_double_number()
    {
        $double = DoubleNumberCombination::get();

        // return response()->json(['success'=>1,'data'=> $double], 200);
        return response()->json(['success'=>1,'data'=> DoubleNumberCombinationResource::collection($double)], 200,[],JSON_NUMERIC_CHECK);


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
     * @param  \App\Http\Requests\StoreDoubleNumberCombinationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDoubleNumberCombinationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DoubleNumberCombination  $doubleNumberCombination
     * @return \Illuminate\Http\Response
     */
    public function show(DoubleNumberCombination $doubleNumberCombination)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DoubleNumberCombination  $doubleNumberCombination
     * @return \Illuminate\Http\Response
     */
    public function edit(DoubleNumberCombination $doubleNumberCombination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDoubleNumberCombinationRequest  $request
     * @param  \App\Models\DoubleNumberCombination  $doubleNumberCombination
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDoubleNumberCombinationRequest $request, DoubleNumberCombination $doubleNumberCombination)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DoubleNumberCombination  $doubleNumberCombination
     * @return \Illuminate\Http\Response
     */
    public function destroy(DoubleNumberCombination $doubleNumberCombination)
    {
        //
    }
}
