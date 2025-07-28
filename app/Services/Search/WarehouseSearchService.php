<?php


namespace App\Services\Search;

use App\DTO\SearchResultDTO;
use App\Http\Resources\Warehouse\WarehouseProductResource;
use App\Http\Resources\Warehouse\WarehouseResource;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;

class WarehouseSearchService
{
    public function search(?string $search, int $perPage = 15): SearchResultDTO
    {
        $warehouseQuery = Warehouse::where('name', 'like', "%{$search}%");
        $warehouseCount = $warehouseQuery->count();

        if ($warehouseCount > 0) {
            $paginated = $warehouseQuery->paginate($perPage);
            $resource = WarehouseResource::collection($paginated)->response()->getData(true);

            return new SearchResultDTO(
                type: 'warehouses',
                message: "Найдено по складам: {$search}",
                data: $resource['data'],
                links: $resource['links'],
                meta: $resource['meta'],
            );
        }

        $products = WarehouseProduct::with(['manufacturer', 'warehouse'])
            ->where('name', 'like', "%{$search}%")
            ->paginate($perPage);

        $resource = WarehouseProductResource::collection($products)->response()->getData(true);

        return new SearchResultDTO(
            type: 'products',
            message: "Найдено по продуктам: {$search}",
            data: $resource['data'],
            links: $resource['links'],
            meta: $resource['meta'],
        );
    }

    public function getAllWithProductCount(int $perPage = 15): SearchResultDTO
    {
        $warehouses = Warehouse::withCount('products')->paginate($perPage);

        $resource = WarehouseResource::collection($warehouses)->response()->getData(true);

        return new SearchResultDTO(
            type: 'warehouses',
            message: "Список складов",
            data: $resource['data'],
            links: $resource['links'],
            meta: $resource['meta'],
        );
    }
}
