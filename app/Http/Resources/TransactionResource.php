<?php

namespace App\Http\Resources;

use App\Models\PlayMaster;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * @property mixed id

 */

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'terminal_id' => $this->terminal_id,
            'terminal_name' => User::find($this->terminal_id)->user_name,
            'play_master_id' => $this->play_master_id,
            'barcode_number' => (PlayMaster::select(DB::raw('substr(play_masters.barcode_number, 1, 8) as barcode_number'))->whereId($this->play_master_id)->first())->barcode_number,
            'old_amount' => $this->old_amount,
            'recharged_amount' => $this->recharged_amount,
            'played_amount' => $this->played_amount,
            'prize_amount' => $this->prize_amount,
            'new_amount' => $this->new_amount
        ];
    }
}
