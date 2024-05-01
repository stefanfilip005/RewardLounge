<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePublicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'remoteId' => $this->id,
            'self' => $this->self,
            'public' => true,
            'anonym' => !$this->showNameInRanking,
        ];

        // Include name and admin flags if not anonym
        if (!$data['anonym']) {
            $data['firstname'] = $this->firstname;
            $data['lastname'] = $this->lastname;
            $data['isAdministrator'] = $this->isAdministrator;
            $data['isModerator'] = $this->isModerator;
            $data['isDeveloper'] = $this->isDeveloper;
        }

        return $data;
    }
}
