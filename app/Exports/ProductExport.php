<?php

namespace App\Exports;

use App\Models\Manufacturer;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $manufacturers = Manufacturer::with([
            'products:id,name,description,manufacturer_id'
        ])->get(['id', 'name']);

        foreach ($manufacturers as $manufacturer) {
            $sheets[] = new ProductSheetExport($manufacturer);
        }

        return $sheets;
    }
}
