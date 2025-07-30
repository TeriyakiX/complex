<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\Warehouse\WarehouseProductResource;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WarehouseProductController extends Controller
{
    public function show(string $id)
    {
        $warehouseProduct = WarehouseProduct::with('warehouse')->findOrFail($id);

        return response()->json([
            'message' => 'Данные товара на складе',
            'data' => new WarehouseProductResource($warehouseProduct),
        ]);
    }
}
