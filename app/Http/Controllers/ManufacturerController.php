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
        $perPage = $request->query('per_page', 15); // можно передать ?per_page=20, по умолчанию 15

        $manufacturers = Manufacturer::withCount('products')->paginate($perPage);

        $data = $manufacturers->getCollection()->map(function ($manufacturer) {
            return [
                'id' => $manufacturer->id,
                'name' => $manufacturer->name,
                'image' => $manufacturer->image
                    ? asset('storage/' . $manufacturer->image)
                    : null,
                'products_count' => $manufacturer->products_count,
            ];
        });

        return response()->json([
            'message' => 'Список производителей',
            'data'    => $data,
            'pagination' => [
                'current_page' => $manufacturers->currentPage(),
                'last_page' => $manufacturers->lastPage(),
                'per_page' => $manufacturers->perPage(),
                'total' => $manufacturers->total(),
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

    public function show(Request $request, Manufacturer $manufacturer)
    {
        $perPage = $request->query('per_page', 10);

        $products = $manufacturer->products()->paginate($perPage);

        return response()->json([
            'message' => 'Данные производителя',
            'data' => [
                'manufacturer' => new ManufacturerResource($manufacturer),
                'products'     => ProductResource::collection($products)->response()->getData(true),
            ],
        ]);
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
