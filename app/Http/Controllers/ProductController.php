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
        // Получаем параметр 'per_page' из query, если нужно менять количество на странице
        $perPage = $request->query('per_page', 10); // по умолчанию 10

        // Получаем пагинацию с eager loading производителя
        $products = Product::with('manufacturer')->paginate($perPage);

        // Возвращаем коллекцию ресурсов с пагинацией
        return ProductResource::collection($products);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create($data);

        return new ProductResource($product->load('manufacturer'));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('manufacturer'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        return new ProductResource($product->load('manufacturer'));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Продукт удалён']);
    }
}
