<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarketplaceRequest;
use App\Http\Resources\MarketplaceResource;
use App\Models\Marketplace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketplaceController extends Controller
{

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 15);

        $marketplaces = Marketplace::paginate($perPage);

        $resourceData = MarketplaceResource::collection($marketplaces)->response()->getData(true);

        return [
            'message' => 'Список маркетплейсов',
            'data'    => $resourceData['data'],
            'links'   => $resourceData['links'],
            'meta'    => $resourceData['meta'],
        ];
    }

    public function show($id)
    {
        $marketplace = Marketplace::findOrFail($id);

        return [
            'message' => 'Данные маркетплейса',
            'data'    => new MarketplaceResource($marketplace),
        ];
    }

    public function store(MarketplaceRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('marketplaces', 'public');
        }

        $marketplace = Marketplace::create($data);

        return response()->json([
            'message' => 'Маркетплейс успешно создан',
            'data'    => new MarketplaceResource($marketplace),
        ], 201);
    }

    public function update(MarketplaceRequest $request, $id)
    {
        $marketplace = Marketplace::findOrFail($id);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($marketplace->image && Storage::disk('public')->exists($marketplace->image)) {
                Storage::disk('public')->delete($marketplace->image);
            }

            $data['image'] = $request->file('image')->store('marketplaces', 'public');
        }

        $marketplace->update($data);

        return response()->json([
            'message' => 'Маркетплейс успешно обновлен',
            'data'    => new MarketplaceResource($marketplace),
        ]);
    }

    public function destroy($id)
    {
        $marketplace = Marketplace::findOrFail($id);

        $marketplace->delete();

        return [
            'message' => 'Маркетплейс успешно удален',
        ];
    }
}
