<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
//use Carbon\Carbon;

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
        //Carbon::setLocale('de');
        $data = [
            'remoteId' => $this->id,
            'self' => $this->self,
            'public' => true,
            'anonym' => !$this->showNameInRanking,
            'haupt' => $this->Mitarbeitertyp,
            'Status' => $this->Status,

            'active' => $this->active,
            //'last_shift_date' => $this->last_shift_date ? Carbon::createFromFormat('Y-m-d H:i:s', $this->last_shift_date)->isoFormat('MMMM Y') : null,
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
