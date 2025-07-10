<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use App\Models\Manufacturer;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ProductImport implements WithMultipleSheets
{
    protected array $sheets = [];

    public function __construct($filePath)
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $manufacturerCache = Manufacturer::pluck('id', 'name')->toArray();

        // Сначала листы с фиксированными производителями
        foreach ($sheetNames as $sheetName) {
            if (!preg_match('/^лист\d+$/iu', $sheetName)) {
                $manufacturerId = $manufacturerCache[$sheetName] ?? null;

                if (!$manufacturerId) {
                    $manufacturer = Manufacturer::create([
                        'id' => (string) Str::uuid(),
                        'name' => $sheetName,
                    ]);
                    $manufacturerId = $manufacturer->id;
                    $manufacturerCache[$sheetName] = $manufacturerId;
                }

                $this->sheets[$sheetName] = new FixedManufacturerImport($manufacturerId);
            }
        }
        foreach ($sheetNames as $sheetName) {
            if (preg_match('/^лист\d+$/iu', $sheetName)) {
                $this->sheets[$sheetName] = new ProductSheetImport();
            }
        }
    }

    public function sheets(): array
    {
        return $this->sheets;
    }
}
