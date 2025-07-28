<?php

namespace App\Http\Resources;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Warehouse\WarehouseProductResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'quantity' => $this->quantity,
            'text' => $this->text,
            'status' => $this->status,
            'product' => $this->whenLoaded('product') ? new ProductResource($this->product) : null,
            'warehouse_product' => $this->whenLoaded('warehouseProduct') ? new WarehouseProductResource($this->warehouseProduct) : null,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
