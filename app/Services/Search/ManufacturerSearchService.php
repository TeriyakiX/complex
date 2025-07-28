<?php

namespace App\Services\Search;

use App\Models\Manufacturer;
use App\Models\Product;
use App\Http\Resources\Manufacturer\ManufacturerResource;
use App\Http\Resources\Product\ProductResource;
use Illuminate\Pagination\LengthAwarePaginator;
use App\DTO\SearchResultDTO;

class ManufacturerSearchService
{
    public function search(?string $search, int $perPage = 15): SearchResultDTO
    {
        if ($search) {
            $manufacturerQuery = Manufacturer::where('name', 'like', "%{$search}%");
            $manufacturerCount = $manufacturerQuery->count();

            if ($manufacturerCount > 0) {
                $paginated = $manufacturerQuery->withCount('products')->paginate($perPage);
                $resource = ManufacturerResource::collection($paginated)
                    ->response()
                    ->getData(true);

                return new SearchResultDTO(
                    type: 'manufacturers',
                    message: "Найдено по производителям: {$search}",
                    data: $resource['data'],
                    links: $resource['links'],
                    meta: $resource['meta'],
                );
            }

            $productQuery = Product::with('manufacturer')
                ->where('name', 'like', "%{$search}%")
                ->paginate($perPage);

            $resource = ProductResource::collection($productQuery)
                ->response()
                ->getData(true);

            return new SearchResultDTO(
                type: 'products',
                message: "Найдено по продуктам: {$search}",
                data: $resource['data'],
                links: $resource['links'],
                meta: $resource['meta'],
            );
        }
        $paginated = Manufacturer::withCount('products')->paginate($perPage);
        $resource = ManufacturerResource::collection($paginated)
            ->response()
            ->getData(true);

        return new SearchResultDTO(
            type: 'manufacturers',
            message: "Список производителей",
            data: $resource['data'],
            links: $resource['links'],
            meta: $resource['meta'],
        );
    }
}
