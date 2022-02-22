<?php

namespace App\Http\Resources;

use App\Models\PayOutSlab;
use App\Models\User;

use App\Models\UserRelationWithOther;
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
            'stockist' => new StockistResource(User::find((UserRelationWithOther::whereTerminalId($this->id)->first())->stockist_id)),
            'payoutSlabId' => $this->pay_out_slab_id,
            'stockistId' => (UserRelationWithOther::whereTerminalId($this->id)->first())->stockist_id,
            'superStockist' => new SuperStockistResource(User::find((UserRelationWithOther::whereTerminalId($this->id)->first())->super_stockist_id)),
            'superStockistId' => (UserRelationWithOther::whereTerminalId($this->id)->first())->super_stockist_id,
        ];
    }
}
