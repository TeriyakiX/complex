<?php

namespace App\Http\Resources\Warehouse;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
            'products' => WarehouseProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
