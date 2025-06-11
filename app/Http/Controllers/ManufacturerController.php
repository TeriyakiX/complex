<?php

namespace App\Http\Controllers;

use App\Http\Requests\ManufacturerRequest;
use App\Http\Resources\ManufacturerResource;
use App\Models\Manufacturer;
use Illuminate\Support\Facades\Storage;

class ManufacturerController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Список производителей',
            'data'    => ManufacturerResource::collection(Manufacturer::all()),
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

    public function show(Manufacturer $manufacturer)
    {
        return response()->json([
            'message' => 'Данные производителя',
            'data'    => new ManufacturerResource($manufacturer),
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
