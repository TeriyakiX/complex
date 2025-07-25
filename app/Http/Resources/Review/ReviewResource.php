<?php

namespace App\Http\Resources\Review;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'text'    => $this->text,
            'rating'  => $this->rating,
            'name'       => $this->name,
            'email'       => $this->email,
            'status'  => $this->status,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
