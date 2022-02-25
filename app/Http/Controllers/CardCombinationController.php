<?php

namespace App\Http\Controllers;

use App\Models\CardCombination;
use App\Http\Requests\StoreCardCombinationRequest;
use App\Http\Requests\UpdateCardCombinationRequest;

class CardCombinationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
