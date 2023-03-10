<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);
        return
        [
			'name' => $this->name,
			'description' => $this->description,
			'description2' => $this->description2,
			'src2' => $this->src2,
			'src3' => $this->src3,
			'price' => $this->price,
			'unsignedinteger' => $this->unsignedinteger,
			'valid_from' => $this->valid_from,
			'valid_to' => $this->valid_to,
        ];
    }
}
