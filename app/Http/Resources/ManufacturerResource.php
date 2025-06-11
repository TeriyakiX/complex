<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ManufacturerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'image'    => $this->image
                ? asset('storage/' . $this->image)
                : null,
            'products' => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
