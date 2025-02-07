<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GiftedPointResource extends JsonResource
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
            'id' => $this->id,
            'receiver_remote_id' => $this->receiver_remote_id,
            'points' => $this->points,
            'gifted_at' => $this->gifted_at->format('Y-m-d'),
            'giver_remote_id' => $this->giver_remote_id,
        ];
    }
}
