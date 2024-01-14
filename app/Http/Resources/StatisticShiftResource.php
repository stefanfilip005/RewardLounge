<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StatisticShiftResource extends JsonResource
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
			'start' => $this->start,
			'end' => $this->end,
			'demandType' => $this->demandType,
			'location' => $this->location,
			'shiftType' => $this->shiftType,
			'points' => $this->points
        ];
    }
}
