<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'text'    => $this->text,
            'user'    => [
                'name'  => $this->user->name,
                'surname' => $this->user->surname,
                'photo'  => $this->user->photo,
            ],
            'status'  => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
