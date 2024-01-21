<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PageViewResource extends JsonResource
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
            'remoteId' => $this->remoteId,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'date' => $this->date,
            'count' => $this->route_count,
        ];
    }
}
