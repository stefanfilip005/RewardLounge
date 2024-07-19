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

        // Generate thumbnail image path by inserting '_thumb' before the file extension
        $pathInfo = pathinfo($imagePath);
        $thumbnailPath = isset($pathInfo['dirname']) && $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '';
        $thumbnailPath .= $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

    
        // Check if the image exists and is readable

        if ($thumbnailPath && Storage::exists('public/'.$thumbnailPath)) {
            $imageData = Storage::get('public/'.$thumbnailPath);
            $base64Image = base64_encode($imageData);
            $mimeType = Storage::mimeType('public/'.$thumbnailPath);
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
			'is_active' => $this->is_active,
            'comment_required' => $this->comment_required,
            'article_number' => $this->article_number,
        ];
    }
}
