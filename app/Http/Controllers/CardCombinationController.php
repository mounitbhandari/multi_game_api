<?php

namespace App\Http\Controllers;

use App\Models\CardCombination;
use App\Http\Requests\StoreCardCombinationRequest;
use App\Http\Requests\UpdateCardCombinationRequest;

class CardCombinationController extends Controller
{

    public function get_all_twelve_card()
    {
        $data = CardCombination::whereCardCombinationTypeId(1)->get();
        return response()->json(['success'=>1,'data'=> $data], 200,[],JSON_NUMERIC_CHECK);
    }

    public function get_all_sixteen_card()
    {
        $data = CardCombination::whereCardCombinationTypeId(2)->get();
        return response()->json(['success'=>1,'data'=> $data], 200,[],JSON_NUMERIC_CHECK);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCardCombinationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCardCombinationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CardCombination  $cardCombination
     * @return \Illuminate\Http\Response
     */
    public function show(CardCombination $cardCombination)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CardCombination  $cardCombination
     * @return \Illuminate\Http\Response
     */
    public function edit(CardCombination $cardCombination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCardCombinationRequest  $request
     * @param  \App\Models\CardCombination  $cardCombination
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCardCombinationRequest $request, CardCombination $cardCombination)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CardCombination  $cardCombination
     * @return \Illuminate\Http\Response
     */
    public function destroy(CardCombination $cardCombination)
    {
        //
    }
}
