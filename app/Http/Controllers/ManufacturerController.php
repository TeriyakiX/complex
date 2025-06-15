<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\Http\Resources\ProductResource;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $manufacturers = Manufacturer::withCount('products')->paginate($perPage);

        $resourceData = ManufacturerResource::collection($manufacturers)->response()->getData(true);

        return response()->json([
            'message' => 'Список производителей',
            'data'    => $resourceData['data'],    // именно массив ресурсов без вложенного data
            'links'   => $resourceData['links'],   // пагинационные ссылки
            'meta'    => $resourceData['meta'],    // мета инфо пагинации
        ]);
    }

    public function show(Request $request, Manufacturer $manufacturer)
    {
        $perPage = $request->query('per_page', 10);
        $products = $manufacturer->products()->paginate($perPage);

        // Присваиваем products как отношение, чтобы Resource мог загрузить
        $manufacturer->setRelation('products', collect($products->items()));

        $productsData = ProductResource::collection($products)->response()->getData(true);

        return response()->json([
            'message' => 'Данные производителя',
            'data' => [
                'manufacturer' => new ManufacturerResource($manufacturer),
                'products' => $productsData['data'],  // только список продуктов
                'links' => $productsData['links'],    // ссылки пагинации по продуктам
                'meta' => $productsData['meta'],      // мета инфо пагинации по продуктам
            ],
        ]);
    }
    public function store(ManufacturerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('manufacturers', 'public');
        }

        $manufacturer = Manufacturer::create($data);

        return response()->json([
            'message' => 'Производитель создан',
            'data'    => new ManufacturerResource($manufacturer),
        ], 201);
    }


    public function update(ManufacturerRequest $request, Manufacturer $manufacturer)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($manufacturer->image && Storage::disk('public')->exists($manufacturer->image)) {
                Storage::disk('public')->delete($manufacturer->image);
            }

            $data['image'] = $request->file('image')->store('manufacturers', 'public');
        }

        $manufacturer->update($data);

        return response()->json([
            'message' => 'Производитель обновлён',
            'data'    => new ManufacturerResource($manufacturer),
        ]);
    }

    public function destroy(Manufacturer $manufacturer)
    {
        if ($manufacturer->image && Storage::disk('public')->exists($manufacturer->image)) {
            Storage::disk('public')->delete($manufacturer->image);
        }

        $manufacturer->delete();

        return response()->json(['message' => 'Производитель удалён']);
    }
}
