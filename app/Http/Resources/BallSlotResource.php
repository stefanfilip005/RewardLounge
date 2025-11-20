<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BallSlotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'mtrNumber'      => $this->mtr_number,
            'label'          => $this->label,
            'helperName'     => $this->helper_name,
            'helperPhone'    => $this->helper_phone,
            'helperEmail'    => $this->helper_email,
            'filledByUserId' => $this->filled_by_user_id,
            'filledAt'       => $this->filled_at?->toIso8601String(),
            'createdAt'      => $this->created_at?->toIso8601String(),
            'updatedAt'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
