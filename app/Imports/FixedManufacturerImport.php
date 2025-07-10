<?php

namespace App\Imports;

use App\Models\Manufacturer;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Row;


class FixedManufacturerImport implements OnEachRow, WithChunkReading, SkipsEmptyRows
{
    protected string $manufacturerId;

    protected array $batch = [];
    protected int $batchSize = 10000;

    protected array $existingProductNames = [];

    public int $inserted = 0;
    public int $skipped = 0;

    public function __construct(string $manufacturerId)
    {
        $this->manufacturerId = $manufacturerId;
        $this->existingProductNames = Product::where('manufacturer_id', $manufacturerId)
            ->pluck('name')
            ->toArray();
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = $data[0] ?? null;
        $description = $data[2] ?? null;

        if (!$name) {
            $this->skipped++;
            return;
        }

        if (in_array($name, $this->existingProductNames)) {
            $this->skipped++;
        } else {
            $this->batch[] = [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description,
                'manufacturer_id' => $this->manufacturerId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $this->inserted++;
            $this->existingProductNames[] = $name;
        }

        if (count($this->batch) >= $this->batchSize) {
            $this->flushBatch();
        }
    }

    protected function flushBatch(): void
    {
        if (!empty($this->batch)) {
            Product::insert($this->batch);
            $this->batch = [];
        }
    }

    public function finalize(): void
    {
        $this->flushBatch();
        Log::info("Лист обработан. Добавлено: {$this->inserted}, пропущено: {$this->skipped}");
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
