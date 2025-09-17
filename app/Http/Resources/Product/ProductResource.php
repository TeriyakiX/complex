<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'slug'        => $this->slug,
            'manufacturer_id' => $this->manufacturer_id,
            'created_at'  => $this->created_at->toDateTimeString(),

            'image' => $this->image
                ? asset('storage/' . $this->image)
                : ($this->manufacturer && $this->manufacturer->image
                    ? asset('storage/' . $this->manufacturer->image)
                    : null),
        ];
    }
}
