<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
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
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'reward_id' => $item->reward_id,
                    'quantity' => $item->quantity,
                    'note' => $item->note,
                    // You might want to include more details about the reward here.
                    // 'reward' => new RewardResource($item->reward)
                ];
            }),
            'total_items' => $this->items->count(),
            // Add more properties as needed
        ];
    }
}
