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
			'remoteId' => $this->remoteId,
			'firstname' => $this->firstname,
			'lastname' => $this->lastname,
			'email' => $this->email,
			'phone' => $this->phone,
			'points' => $this->points,
			'lastPointCalculation' => $this->lastPointCalculation,

			'isAdministrator' => $this->isAdministrator,
			'isModerator' => $this->isModerator,
			'isDeveloper' => $this->isDeveloper,
			'showNameInRanking' => $this->showNameInRanking,
        ];
    }
}
