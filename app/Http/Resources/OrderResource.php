<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

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
            'note' => $this->note,

            'created_at_datetime' => $this->created_at_datetime,
            'state_1_datetime' => $this->state_1_datetime,
            'state_1_user_id' => $this->state_1_user_id,
            'state_2_datetime' => $this->state_2_datetime,
            'state_2_user_id' => $this->state_2_user_id,
            'state_3_datetime' => $this->state_3_datetime,
            'state_3_user_id' => $this->state_3_user_id,
            'state_4_datetime' => $this->state_4_datetime,
            'state_4_user_id' => $this->state_4_user_id,
            'state_5_datetime' => $this->state_5_datetime,
            'state_5_user_id' => $this->state_5_user_id,
            
            'order_items' => $this->orderItems->transform(function ($orderItem) {

                $imagePath = $orderItem->src1;
                $pathInfo = pathinfo($imagePath);
                $thumbnailPath = isset($pathInfo['dirname']) && $pathInfo['dirname'] !== '.' ? $pathInfo['dirname'] . '/' : '';
                $thumbnailPath .= $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];
        
                if ($thumbnailPath && Storage::exists('public/'.$thumbnailPath)) {
                    $imageData = Storage::get('public/'.$thumbnailPath);
                    $base64Image = base64_encode($imageData);
                    $mimeType = Storage::mimeType('public/'.$thumbnailPath);
                    $imageSrc = 'data:' . $mimeType . ';base64,' . $base64Image;
                } else {
                    $imageSrc = null; // or path to a default image
                }


                return [
                    'id' => $orderItem->id,
                    'reward_id' => $orderItem->reward_id,
                    'quantity' => $orderItem->quantity,
                    'note' => $orderItem->note,
                    'name' => $orderItem->name,
                    'slogan' => $orderItem->slogan,
                    'description' => $orderItem->description,
                    'src1' => $imageSrc,
                    'points' => $orderItem->points,
                    'euro' => $orderItem->euro,
                    'article_number' => $orderItem->article_number,
                ];
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
