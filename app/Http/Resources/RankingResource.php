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
			'remoteId' => $this->remoteId,
			'year' => $this->year,
			'place' => $this->place,
			'points' => $this->points,
			'pointsForNext' => $this->pointsForNext
        ];
    }
}
