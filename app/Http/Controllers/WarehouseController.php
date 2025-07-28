<?php

namespace App\Http\Controllers;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Warehouse\WarehouseProductResource;
use App\Http\Resources\Warehouse\WarehouseResource;
use App\Imports\Warehouse\WarehouseProductImport;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use App\Services\Search\WarehouseSearchService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseController extends Controller
{
    protected WarehouseSearchService $warehouseService;

    public function __construct(WarehouseSearchService $warehouseSearchService)
    {
        $this->warehouseSearchService = $warehouseSearchService;
    }

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);
        $search = $request->query('search');

        if ($search) {
            $result = $this->warehouseSearchService->search($search, $perPage);
            return response()->json($result);
        }

        $result = $this->warehouseSearchService->getAllWithProductCount($perPage);
        return response()->json($result);
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
