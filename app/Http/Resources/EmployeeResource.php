<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
			'anonym' => false,
			'self' => $this->self,
			'remoteId' => $this->remoteId,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'email' => $this->email,
			'phone' => $this->phone,
			'points' => $this->points,
			'shifts' => $this->shifts,
			'lastPointCalculation' => $this->lastPointCalculation,

			'isAdministrator' => $this->isAdministrator ? true : false,
			'isModerator' => $this->isModerator ? true : false,
			'isDeveloper' => $this->isDeveloper ? true : false,
			'showNameInRanking' => $this->showNameInRanking,
			'picture_base64' => $this->picture_base64,
        ];
    }
}
