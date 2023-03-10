<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
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
			'employeeId' => $this->employeeId,
			'start' => $this->start,
			'end' => $this->end,
			'usage' => $this->usage,
			'location' => $this->location,
			'duration' => $this->duration,
        ];
    }
}
