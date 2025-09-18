<?php

namespace App\Imports\Produtct;

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

        foreach ($sheetNames as $sheetName) {
            $importer = new ProductSheetImport();
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
