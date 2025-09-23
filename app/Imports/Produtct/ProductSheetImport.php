<?php

namespace App\Imports\Produtct;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;

class ProductSheetImport implements OnEachRow, WithChunkReading, SkipsEmptyRows
{
    protected array $batch = [];
    protected int $batchSize = 1000;

    protected array $existingProductsByManufacturer = [];

    public int $inserted = 0;
    public int $skipped = 0;

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        if ($rowIndex < 2) return;

        $data = array_values($row->toArray());

        $name = isset($data[0]) ? trim((string)$data[0]) : null;
        $manufacturerName = isset($data[1]) ? trim((string)$data[1]) : null;
        $description = isset($data[2]) ? trim((string)$data[2]) : null;

        if (!$name || !$manufacturerName) {
            $this->skipped++;
            return;
        }

        $manufacturer = Manufacturer::firstOrCreate(
            ['name' => $manufacturerName],
            ['id' => (string) Str::uuid()]
        );
        $manufacturerId = $manufacturer->id;

        if (!isset($this->existingProductsByManufacturer[$manufacturerId])) {
            $this->existingProductsByManufacturer[$manufacturerId] = Product::where('manufacturer_id', $manufacturerId)
                ->pluck('name')
                ->map(fn($n) => mb_strtolower(trim($n)))
                ->toArray();
        }

        if (in_array(mb_strtolower($name), $this->existingProductsByManufacturer[$manufacturerId])) {
            $this->skipped++;
            return;
        }

        $this->addProduct($name, $description, $manufacturerId);
    }

    protected function addProduct(string $name, ?string $description, string $manufacturerId): void
    {
        $id = (string) Str::uuid();
        $slugBase = $name . ' ' . ($description ?? '');
        $slug = Str::slug($slugBase . '-' . substr($id, 0, 8));

        $this->batch[] = [
            'id' => $id,
            'name' => $name,
            'description' => $description,
            'manufacturer_id' => $manufacturerId,
            'slug' => $slug,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $this->existingProductsByManufacturer[$manufacturerId][] = mb_strtolower($name);
        $this->inserted++;

        if (count($this->batch) >= $this->batchSize) {
            $this->flushBatch();
        }
    }

    protected function flushBatch(): void
    {
        if (!empty($this->batch)) {
            DB::transaction(function () {
                Product::insert($this->batch);
            });
            $this->batch = [];
        }
    }

    public function finalize(): void
    {
        $this->flushBatch();
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
