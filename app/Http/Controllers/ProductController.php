<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $products = Product::with('manufacturer')->paginate($perPage);

        return ProductResource::collection($products)->additional([
            'message' => 'Список продуктов',
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create($data);

        return response()->json([
            'message' => 'Продукт создан',
            'data'    => new ProductResource($product->load('manufacturer')),
        ], 201);
    }

    public function show(Product $product)
    {
        return response()->json([
            'message' => 'Данные продукта',
            'data'    => new ProductResource($product->load('manufacturer')),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return response()->json([
            'message' => 'Продукт обновлён',
            'data'    => new ProductResource($product->load('manufacturer')),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Продукт удалён']);
    }
}
