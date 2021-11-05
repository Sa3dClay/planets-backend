<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($req)
    {
        return [
            'id'            =>  $this->id,
            'name'          =>  $this->name,
            'role'          =>  $this->role,
            'email'         =>  $this->email,
            'planet'        =>  $this->planet,
            'created_at'    =>  $this->created_at,
            'updated_at'    =>  $this->updated_at,
        ];
    }
}
