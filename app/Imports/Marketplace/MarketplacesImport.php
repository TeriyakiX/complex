<?php

namespace App\Imports\Marketplace;

use App\Models\Marketplace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Row;

class MarketplacesImport implements OnEachRow, WithStartRow
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
        if (!$name) return;

        $coordinate = 'B' . $rowIndex;
        $imagePath = null;

        if (isset($this->drawingsByCoordinate[$coordinate])) {
            $drawing = $this->drawingsByCoordinate[$coordinate];
            $imageContents = file_get_contents($drawing->getPath());
            $extension = pathinfo($drawing->getPath(), PATHINFO_EXTENSION);
            $filename = 'marketplaces/' . Str::uuid() . '.' . $extension;

            Storage::disk('public')->put($filename, $imageContents);
            $imagePath = $filename;
        }

        Marketplace::create([
            'name'  => $name,
            'image' => $imagePath,
        ]);
    }
}
