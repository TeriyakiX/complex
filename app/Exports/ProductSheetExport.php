<?php

namespace App\Exports;

use App\Models\Manufacturer;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductSheetExport implements FromArray, WithTitle
{
    protected Manufacturer $manufacturer;

    public function __construct(Manufacturer $manufacturer)
    {
        $this->manufacturer = $manufacturer;
    }

    public function array(): array
    {
        $data = [];
        $data[] = ['name', 'manufacturer', 'description'];

        foreach ($this->manufacturer->products as $product) {
            $data[] = [
                $product->name ?? '',
                $this->manufacturer->name ?? '',
                $product->description ?? '',
            ];
        }

        return $data;
    }

    public function title(): string
    {
        // Ограничение Excel на 31 символ
        return mb_substr($this->manufacturer->name, 0, 31);
    }
}
