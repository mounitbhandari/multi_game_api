<?php

namespace App\Http\Resources;

use App\Models\PayOutSlab;
use App\Models\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\StockistResource;

/**
 * @property mixed id
 * @property mixed user_name
 * @property mixed closing_balance
 * @property mixed email
 */
class TerminalResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'terminalId' => $this->id,
            'terminalName' => $this->user_name,
            'pin' => $this->email,
            'balance' =>$this->closing_balance,
            'commission' =>$this->commission,
            'stockist' => is_null($this->stockist_id) ? 'empty':new UserResource(User::find($this->stockist_id)),
            'payoutSlabId' => $this->pay_out_slab_id,
            'stockistId' => $this->stockist_id,
            'superStockist' => is_null($this->super_stockist_id) ? 'empty':new UserResource(User::find($this->super_stockist_id)),
            'superStockistId' => $this->super_stockist_id,
        ];
    }
}
