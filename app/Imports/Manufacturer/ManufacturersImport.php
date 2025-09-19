<?php

namespace App\Imports\Manufacturer;

use App\Models\Manufacturer;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class ManufacturersImport implements OnEachRow, WithStartRow
{
    private array $drawingsByCoordinate;

    public function __construct(array $drawingsByCoordinate = [])
    {
        $this->drawingsByCoordinate = $drawingsByCoordinate;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row = $row->toArray();

        $name = $row[0] ?? null;
        if (!$name) {
            return;
        }

        $description = $row[1] ?? null;
        $imagePath = null;

        $coordinateC = 'C' . $rowIndex;
        if (isset($this->drawingsByCoordinate[$coordinateC])) {
            $drawing = $this->drawingsByCoordinate[$coordinateC];
            if ($drawing instanceof Drawing) {
                $imageContents = file_get_contents($drawing->getPath());
                $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
                $filename = 'manufacturers/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($filename, $imageContents);
                $imagePath = $filename;
            }
        }

        $manufacturer = Manufacturer::firstOrNew(['name' => $name]);

        if ($description !== null) {
            $manufacturer->description = $description;
        }

        if ($imagePath !== null) {
            $manufacturer->image = $imagePath;
        }

        $manufacturer->save();
    }
}
