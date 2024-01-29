<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InfoblattResource extends JsonResource
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
            'year' => $this->year,
            '01' => $this->m01,
            '02' => $this->m02,
            '03' => $this->m03,
            '04' => $this->m04,
            '05' => $this->m05,
            '06' => $this->m06,
            '07' => $this->m07,
            '08' => $this->m08,
            '09' => $this->m09,
            '10' => $this->m10,
            '11' => $this->m11,
            '12' => $this->m12,
        ];
    }
}
