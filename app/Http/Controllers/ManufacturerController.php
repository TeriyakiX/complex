<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\Manufacturer\ManufacturerResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Services\Search\ManufacturerSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    public function index(Request $request, ManufacturerSearchService $searchService)
    {
        $search = $request->query('search');
        $perPage = (int) $request->query('per_page', 15);

        $result = $searchService->search($search, $perPage);

        return response()->json([
            'message' => $result->message,
            'type' => $result->type,
            'data' => $result->data,
            'links' => $result->links,
            'meta' => $result->meta,
        ]);
    }

    public function show(Request $request, Manufacturer $manufacturer)
    {
        $perPage = $request->query('per_page', 15);
        $products = $manufacturer->products()->paginate($perPage);

        $manufacturer->setRelation('products', collect($products->items()));

        $productsData = ProductResource::collection($products)->response()->getData(true);

        return response()->json([
            'message' => 'Данные производителя',
            'data' => new ManufacturerResource($manufacturer),
            'links' => $productsData['links'],
            'meta' => $productsData['meta'],
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
