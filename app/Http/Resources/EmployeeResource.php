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
            'public' => false,
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
            'isDienstfuehrer' => $this->isDienstfuehrer ? true : false,
			'isDeveloper' => $this->isDeveloper ? true : false,
			'showNameInRanking' => $this->showNameInRanking,

            'last_shift_date' => $this->last_shift_date,
            'next_shift_date' => $this->next_shift_date,
            'last_sms_sent' => $this->last_sms_sent,
            'sms_count' => $this->sms_count,
            'employeeType' => $this->employeeType,
        ];
    }
}
