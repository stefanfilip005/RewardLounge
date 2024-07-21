<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
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
            'id' => $this->course_id,
            'name' => $this->name,
            'info' => $this->info,
            'date' => $this->date->format('d.m.Y'), 
            'start_time' => $this->von,
            'end_time' => $this->bis,
            'training_width' => $this->breitenausbildung
        ];
    }
}
