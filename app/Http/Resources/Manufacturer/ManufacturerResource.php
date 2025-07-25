<?php

namespace App\Http\Resources\Manufacturer;

use App\Http\Resources\Product\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ManufacturerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'image' => $this->image
                ? asset('storage/' . $this->image)
                : null,

            // только если загружено products
            'products' => ProductResource::collection($this->whenLoaded('products')),

            // только если есть подсчёт продуктов
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
        ];
    }
}
