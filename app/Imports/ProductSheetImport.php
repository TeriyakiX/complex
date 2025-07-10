<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Manufacturer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ProductSheetImport implements OnEachRow, WithChunkReading, SkipsEmptyRows
{
    protected array $manufacturersCache = [];
    protected ?string $lastManufacturerName = null;
    protected ?string $lastManufacturerId = null;

    protected array $batch = [];
    protected int $batchSize = 1000;

    public int $inserted = 0;
    public int $skipped = 0;

    public function __construct()
    {
        $this->manufacturersCache = Manufacturer::pluck('id', 'name')->toArray();
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $name = $data[0] ?? null;
        $manufacturerName = $data[1] ?? null;
        $description = $data[2] ?? null;

        if (!$name || !$manufacturerName) {
            $this->skipped++;
            return;
        }

        if ($manufacturerName !== $this->lastManufacturerName) {
            if (!isset($this->manufacturersCache[$manufacturerName])) {
                $manufacturer = Manufacturer::create([
                    'id' => (string) Str::uuid(),
                    'name' => $manufacturerName,
                ]);
                $this->manufacturersCache[$manufacturerName] = $manufacturer->id;
            }
            $this->lastManufacturerName = $manufacturerName;
            $this->lastManufacturerId = $this->manufacturersCache[$manufacturerName];
        }

        $manufacturerId = $this->lastManufacturerId;

        $exists = Product::where('name', $name)
            ->where('manufacturer_id', $manufacturerId)
            ->exists();

        if ($exists) {
            $this->skipped++;
        } else {
            $this->batch[] = [
                'id' => (string) Str::uuid(),
                'name' => $name,
                'description' => $description,
                'manufacturer_id' => $manufacturerId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $this->inserted++;
        }

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
        Log::info("Лист обработан. Добавлено: {$this->inserted}, пропущено: {$this->skipped}");
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}


