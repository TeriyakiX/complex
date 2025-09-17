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
        if (!$name) return;

        $description = null;
        $imagePath = null;

        // Проверяем сначала колонку B
        $coordinateB = 'B' . $rowIndex;
        $coordinateC = 'C' . $rowIndex;

        if (isset($this->drawingsByCoordinate[$coordinateB])) {
            $drawing = $this->drawingsByCoordinate[$coordinateB];
        } elseif (isset($this->drawingsByCoordinate[$coordinateC])) {
            $drawing = $this->drawingsByCoordinate[$coordinateC];
        } else {
            $drawing = null;
        }

        if ($drawing instanceof Drawing) {
            $imageContents = file_get_contents($drawing->getPath());
            $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
            $filename = 'manufacturers/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($filename, $imageContents);
            $imagePath = $filename;
        } else {
            // Если рисунка нет, берем текст из B или C как описание
            $description = $row[1] ?? $row[2] ?? null;
        }

        // Обновляем или создаем производителя
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
