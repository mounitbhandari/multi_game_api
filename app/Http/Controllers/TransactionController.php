<?php

namespace App\Http\Controllers;

use App\Http\Resources\TerminalResource;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{

    public function getTransaction($id)
    {
        $transaction = Transaction::whereTerminalId($id)->orderBy('created_at', 'desc')->get();
        return response()->json(['success'=>1,'data'=>TransactionResource::collection($transaction)], 200);
//        return response()->json(['success'=>$id,'data'=>$transaction], 200);
    }

    public function mailTransactionOneMonth($id)
    {
        $date = Carbon::today()->subDays(30)->format('Y-m-d');
        $transaction = json_decode(json_encode(TransactionResource::collection(Transaction::whereTerminalId($id)->whereRaw('date(created_at) >= ?', [$date])->orderBy('created_at', 'desc')->get())));

        $to_email = "priyamghosh19@gmail.com";
//        $data = array('name'=>"Test Mail" ,"transactions" => $transaction);
        $data = array('barcodeNumber'=>$transaction[0]->barcode_number ,"transactions" => $transaction);
        $mail_title="Transaction report one month";
        Mail::send('emails.transaction_email', $data, function($message) use ($to_email,$mail_title) {
            $message->to($to_email)->subject($mail_title);
            $message->bcc("ginfotech197@gmail.com");
            $message->from('no-reply@rkng.xyz',"Royal King");
        });

        return $transaction;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTransactionRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTransactionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTransactionRequest  $request
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transaction  $transaction
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
