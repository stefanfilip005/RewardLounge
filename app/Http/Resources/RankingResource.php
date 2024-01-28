<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RankingResource extends JsonResource
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
			'year' => $this->year,
			'place' => $this->place,
			'points' => $this->points,
			'location' => $this->location,
			'pointsForNext' => $this->pointsForNext
        ];
    }
}
