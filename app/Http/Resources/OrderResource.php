<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'remoteId' => $this->remoteId,
            'total_points' => $this->total_points,
            'state' => $this->state,
            'order_items' => $this->orderItems->transform(function ($orderItem) {
                return [
                    'id' => $orderItem->id,
                    'reward_id' => $orderItem->reward_id,
                    'quantity' => $orderItem->quantity,
                    'note' => $orderItem->note,
                    // Include any additional fields as needed from the orderItem
                ];
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            // Include any additional fields as needed
        ];
    }
}
