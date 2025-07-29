<?php

namespace App\Services\Search;

use App\Http\Resources\Warehouse\WarehouseProductResource;
use App\Models\Manufacturer;
use App\Models\Product;
use App\Http\Resources\Manufacturer\ManufacturerResource;
use App\Http\Resources\Product\ProductResource;
use App\Models\WarehouseProduct;
use Illuminate\Pagination\LengthAwarePaginator;
use App\DTO\SearchResultDTO;
use Illuminate\Support\Facades\DB;

class ManufacturerSearchService
{
    public function search(?string $search, int $perPage = 15): SearchResultDTO
    {
        if ($search) {
            $manufacturerQuery = Manufacturer::where('name', 'like', "%{$search}%");
            $manufacturerCount = $manufacturerQuery->count();

            if ($manufacturerCount > 0) {
                $paginated = $manufacturerQuery
                    ->withCount(['products', 'warehouseProducts'])
                    ->paginate($perPage);

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
                ->where('name', 'like', "%{$search}%");

            $productCount = $productQuery->count();

            if ($productCount > 0) {
                $paginated = $productQuery->paginate($perPage);
                $resource = ProductResource::collection($paginated)
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

            $warehouseProductQuery = WarehouseProduct::with(['manufacturer', 'warehouse'])
                ->where('name', 'like', "%{$search}%");

            $warehouseProductCount = $warehouseProductQuery->count();

            if ($warehouseProductCount > 0) {
                $paginated = $warehouseProductQuery->paginate($perPage);
                $resource = WarehouseProductResource::collection($paginated)
                    ->response()
                    ->getData(true);

                return new SearchResultDTO(
                    type: 'warehouse_products',
                    message: "Найдено по складу: {$search}",
                    data: $resource['data'],
                    links: $resource['links'],
                    meta: $resource['meta'],
                );
            }

            // Ничего не найдено
            return new SearchResultDTO(
                type: 'none',
                message: "Ничего не найдено по запросу: {$search}",
                data: [],
                links: [],
                meta: [],
            );
        }

        $query = Manufacturer::withCount(['products', 'warehouseProducts'])
            ->orderByDesc(DB::raw('(COALESCE(products_count, 0) + COALESCE(warehouse_products_count, 0))'));

        $paginated = $query->paginate($perPage);

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
