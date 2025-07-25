<?php

namespace App\Http\Resources\Warehouse;

use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseProductResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'manufacturer_id' => $this->manufacturer_id,
            'manufacturer_name' => $this->manufacturer->name ?? null,
            'stock' => $this->stock,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
