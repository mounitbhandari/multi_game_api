<?php

namespace App\Http\Resources;

use App\Models\AndarNumber;
use App\Models\BaharNumber;
use App\Models\SingleNumber;
use Illuminate\Http\Resources\Json\JsonResource;

class DoubleNumberCombinationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'doubleNumberCombinationId' => $this->id,
            'singleNumber' => new SingleNumberSimpleResource(SingleNumber::find( $this->single_number_id,)),
            'doubleNumber' => $this->double_number,
            'andarNumber' => new AndarResource(AndarNumber::find($this->andar_number_id)),
            'baharNumber' => new BaharResource(BaharNumber::find($this->bahar_number_id)),
        ];
    }
}
