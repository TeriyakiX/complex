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
    private $drawingsByCoordinate;

    public function __construct(array $drawingsByCoordinate)
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

        $coordinate = 'B' . $rowIndex;
        $imagePath = null;

        if (isset($this->drawingsByCoordinate[$coordinate])) {
            /** @var Drawing $drawing */
            $drawing = $this->drawingsByCoordinate[$coordinate];
            $imageContents = file_get_contents($drawing->getPath());
            $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
            $filename = 'manufacturers/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($filename, $imageContents);
            $imagePath = $filename;
        }

        $manufacturer = Manufacturer::where('name', $name)->first();

        if ($manufacturer) {
            if ($imagePath !== null) {
                $manufacturer->image = $imagePath;
                $manufacturer->save();
            }
        } else {
            Manufacturer::create([
                'name' => $name,
                'image' => $imagePath,
            ]);
        }
    }
}
