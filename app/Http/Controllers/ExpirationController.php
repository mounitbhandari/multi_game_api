<?php

namespace App\Http\Controllers;

use App\Models\Expiration;
use App\Http\Requests\StoreExpirationRequest;
use App\Http\Requests\UpdateExpirationRequest;

class ExpirationController extends Controller
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
     * @param  \App\Http\Requests\StoreExpirationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreExpirationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Expiration  $expiration
     * @return \Illuminate\Http\Response
     */
    public function show(Expiration $expiration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Expiration  $expiration
     * @return \Illuminate\Http\Response
     */
    public function edit(Expiration $expiration)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateExpirationRequest  $request
     * @param  \App\Models\Expiration  $expiration
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateExpirationRequest $request, Expiration $expiration)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Expiration  $expiration
     * @return \Illuminate\Http\Response
     */
    public function destroy(Expiration $expiration)
    {
        //
    }
}
