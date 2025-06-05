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
        return ManufacturerResource::collection(Manufacturer::all());
    }

    public function store(ManufacturerRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('manufacturers', 'public');
        }

        $manufacturer = Manufacturer::create($data);

        return new ManufacturerResource($manufacturer);
    }

    public function show(Manufacturer $manufacturer)
    {
        return new ManufacturerResource($manufacturer);
    }

    public function update(ManufacturerRequest $request, Manufacturer $manufacturer)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($manufacturer->image) {
                Storage::disk('public')->delete($manufacturer->image);
            }
            $data['image'] = $request->file('image')->store('manufacturers', 'public');
        }

        $manufacturer->update($data);

        return new ManufacturerResource($manufacturer);
    }

    public function destroy(Manufacturer $manufacturer)
    {
        if ($manufacturer->image) {
            Storage::disk('public')->delete($manufacturer->image);
        }
        $manufacturer->delete();

        return response()->json(['message' => 'Производитель удалён']);
    }
}
