<?php

namespace App\Imports\Warehouse;

use App\Models\Manufacturer;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class WarehouseProductImport implements ToCollection
{
    public function __construct(protected Warehouse $warehouse) {}

    public function collection(Collection $rows)
    {
        // Пропускаем первую строку — заголовки
        $rows = $rows->slice(1);

        foreach ($rows as $row) {
            $name = trim($row[0]); // Название
            $manufacturerName = trim($row[1]); // Производитель
            $stock = intval($row[2]); // На складе

            $manufacturer = Manufacturer::firstOrCreate(
                ['name' => $manufacturerName],
                ['image' => null]
            );

            WarehouseProduct::updateOrCreate(
                [
                    'warehouse_id' => $this->warehouse->id,
                    'name' => $name,
                    'manufacturer_id' => $manufacturer->id,
                ],
                ['stock' => $stock]
            );
        }
    }
}
