<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'manufacturer'   => [
                'id'   => $this->manufacturer->id,
                'name' => $this->manufacturer->name,
                'image' => $this->manufacturer->image
                    ? asset('storage/' . $this->manufacturer->image)
                    : null,
            ],
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}
