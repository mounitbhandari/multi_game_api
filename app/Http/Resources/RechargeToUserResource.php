<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class RechargeToUserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'rechargedTo' => new UserResource(User::find($this->beneficiary_uid)),
            'rechargedby' => new UserResource(User::find($this->recharge_done_by_uid)),
            'oldAmount' => $this->old_amount,
            'rechargedAmount' => $this->amount,
            'newAmount' => $this->new_amount,
            'dateAndTime' => $this->created_at
        ];
    }
}
