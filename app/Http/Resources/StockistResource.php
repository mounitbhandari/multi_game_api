<?php

namespace App\Http\Resources;

use App\Models\UserRelationWithOther;
use Illuminate\Http\Resources\Json\JsonResource;
use function PHPUnit\Framework\isNull;

class StockistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'userId' => $this->id,
            'userName' => $this->user_name,
            'pin' => $this->email,
//            'userTypeId' => ($this->user_type)->id,
            'userTypeId' => $this->user_type_id,
            'balance' => $this->closing_balance,
            'superStockistId' => is_Null(UserRelationWithOther::whereStockistId($this->id)->whereActive(1)->first())? 'null': (UserRelationWithOther::whereStockistId($this->id)->whereActive(1)->first())->super_stockist_id
        ];
    }
}
