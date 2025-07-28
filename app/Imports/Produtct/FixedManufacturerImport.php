<?php

namespace App\Imports\Produtct;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
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
    protected int $batchSize = 1000;
    protected array $existingProductNames;

    public int $inserted = 0;
    public int $skipped = 0;

    public function __construct(string $manufacturerId)
    {
        $this->manufacturerId = $manufacturerId;
        $this->existingProductNames = Product::where('manufacturer_id', $manufacturerId)
            ->pluck('name')
            ->map(fn($name) => mb_strtolower(trim($name)))
            ->toArray();
    }

    public function onRow(Row $row): void
    {
        $data = array_values($row->toArray());

        $name = isset($data[0]) ? trim($data[0]) : null;
        $description = isset($data[2]) ? trim($data[2]) : null;

        if (!$name) {
            $this->skipped++;
            return;
        }

        $lowerName = mb_strtolower($name);

        if (in_array($lowerName, $this->existingProductNames)) {
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
            $this->existingProductNames[] = $lowerName;
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
        Log::info("Лист {$this->manufacturerId} обработан. Добавлено: {$this->inserted}, пропущено: {$this->skipped}");
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
