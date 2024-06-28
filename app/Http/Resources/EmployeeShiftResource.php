<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeShiftResource extends JsonResource
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
            'employee_id' => $this->employee_id,
            'location' => $this->location,
            'time_groups' => [
                'VM' => $this->VM,
                'VM_norm' => $this->VM_norm,
                'NM' => $this->NM,
                'NM_norm' => $this->NM_norm,
                'NIGHT' => $this->NIGHT,
                'NIGHT_norm' => $this->NIGHT_norm,
            ],
            'demand_type_groups' => [
                'NEF' => $this->NEF,
                'NEF_norm' => $this->NEF_norm,
                'RTW' => $this->RTW,
                'RTW_norm' => $this->RTW_norm,
                'KTW' => $this->KTW,
                'KTW_norm' => $this->KTW_norm,
                'BKTW' => $this->BKTW,
                'BKTW_norm' => $this->BKTW_norm,
            ],
            'weekdays' => array_map(function($day) {
                return $this->{"weekday_$day"} . '_norm';
            }, range(0, 6)),
        ];
    }
}
