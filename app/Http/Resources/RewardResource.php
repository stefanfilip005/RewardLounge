<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class RewardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $imagePath = $this->src1;
    
        // Check if the image exists and is readable

        if ($imagePath && Storage::exists('public/'.$imagePath)) {
            $imageData = Storage::get('public/'.$imagePath);
            $base64Image = base64_encode($imageData);
            $mimeType = Storage::mimeType('public/'.$imagePath);
            $imageSrc = 'data:' . $mimeType . ';base64,' . $base64Image;
        } else {
            $imageSrc = null; // or path to a default image
        }

        //return parent::toArray($request);
        return
        [
			'id' => $this->id,
			'name' => $this->name,
			'slogan' => $this->slogan,
			'description' => $this->description,
            'src1' => $imageSrc,
			'points' => $this->points,
			'euro' => $this->euro,
			'valid_from' => $this->valid_from,
			'valid_to' => $this->valid_to,
        ];
    }
}
