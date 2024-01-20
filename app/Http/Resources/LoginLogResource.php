<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginLogResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'logged_in_at' => $this->logged_in_at,
            'ip_address' => $this->ip_address,
        ];
    }
}
