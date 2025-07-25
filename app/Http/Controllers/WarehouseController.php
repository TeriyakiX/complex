<?php

namespace App\Http\Controllers;

use App\Http\Resources\Warehouse\WarehouseProductResource;
use App\Http\Resources\Warehouse\WarehouseResource;
use App\Imports\Warehouse\WarehouseProductImport;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $warehouses = Warehouse::withCount('products')->paginate($perPage);

        $resourceData = WarehouseResource::collection($warehouses)
            ->response()
            ->getData(true);

        return response()->json([
            'message' => 'Список складов',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ]);
    }

    public function show(Request $request, Warehouse $warehouse)
    {
        $perPage = $request->query('per_page', 15);

        $products = $warehouse->products()->with('manufacturer')->paginate($perPage);

        $warehouse->setRelation('products', collect($products->items()));

        $productsData = WarehouseProductResource::collection($products)->response()->getData(true);

        return response()->json([
            'message' => 'Данные склада: ' . $warehouse->name,
            'data' => new WarehouseResource($warehouse),
            'links' => $productsData['links'],
            'meta' => $productsData['meta'],
        ]);
    }

    public function uploadImage(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('warehouses', 'public');

        $warehouse->update(['image' => $path]);

        return response()->json([
            'message' => 'Картинка обновлена',
            'image' => asset('storage/' . $path),
        ]);
    }

    public function import(Request $request, Warehouse $warehouse)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ]);

        Excel::import(new WarehouseProductImport($warehouse), $request->file('file'));

        return response()->json(['message' => 'Импорт завершён']);
    }
}
