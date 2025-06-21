<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketplaceResource;
use App\Models\Marketplace;
use Illuminate\Http\Request;

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
}
