<?php

namespace App\Imports\Produtct;

use App\Models\Manufacturer;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class ProductImport implements WithMultipleSheets
{
    protected array $sheets = [];
    protected array $importers = [];

    public function __construct($filePath)
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();

        $manufacturerCache = Manufacturer::pluck('id', 'name')->toArray();

        foreach ($sheetNames as $sheetName) {
            $manufacturerId = $manufacturerCache[$sheetName] ?? null;

            if (!$manufacturerId) {
                $manufacturer = Manufacturer::create([
                    'id' => (string) Str::uuid(),
                    'name' => $sheetName,
                ]);
                $manufacturerId = $manufacturer->id;
                $manufacturerCache[$sheetName] = $manufacturerId;
            }

            $importer = new FixedManufacturerImport($manufacturerId);
            $this->sheets[$sheetName] = $importer;
            $this->importers[] = $importer;
        }
    }

    public function sheets(): array
    {
        return $this->sheets;
    }

    public function finalizeAll(): void
    {
        foreach ($this->importers as $importer) {
            $importer->finalize();
        }
    }
}
