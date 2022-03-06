<?php

namespace App\Http\Resources;

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
            'singleNumberId' => $this->single_number_id,
            'doubleNumber' => $this->double_number,
            'visibleDoubleNumber' => $this->visible_double_number,

        ];
    }
}
