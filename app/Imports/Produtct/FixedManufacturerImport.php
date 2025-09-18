<?php

namespace App\Imports\Produtct;

use App\Models\Manufacturer;
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
    protected ?string $manufacturerId = null;
    protected bool $manufacturerChecked = false;

    protected array $batch = [];
    protected int $batchSize = 1000;
    protected array $existingProductNames = [];

    public int $inserted = 0;
    public int $skipped = 0;

    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $data = array_values($row->toArray());

        // Берем производителя из B2 (вторая строка, колонка B)
        if ($rowIndex === 2 && !$this->manufacturerChecked) {
            $manufacturerName = isset($data[1]) ? trim($data[1]) : null;

            if (!$manufacturerName) {
                Log::warning("Не найден производитель в ячейке B2");
                return;
            }

            $manufacturer = Manufacturer::firstOrCreate(
                ['name' => $manufacturerName],
                ['id' => (string) Str::uuid()]
            );

            $this->manufacturerId = $manufacturer->id;
            $this->existingProductNames = Product::where('manufacturer_id', $this->manufacturerId)
                ->pluck('name')
                ->map(fn($n) => mb_strtolower(trim($n)))
                ->toArray();

            $this->manufacturerChecked = true;
            return; // строку B2 не обрабатываем как продукт
        }

        // продукты начинаются только после второй строки
        if ($rowIndex <= 2 || !$this->manufacturerId) {
            return;
        }

        $name = $data[0] ?? null;
        $description = $data[2] ?? null;

        if (!$name) {
            $this->skipped++;
            return;
        }

        $lowerName = mb_strtolower(trim($name));

        if (in_array($lowerName, $this->existingProductNames)) {
            $this->skipped++;
        } else {
            $id = (string) Str::uuid();
            $slugBase = $name . ' ' . ($description ?? '');
            $slug = Str::slug($slugBase . '-' . substr($id, 0, 8));

            $this->batch[] = [
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'manufacturer_id' => $this->manufacturerId,
                'slug' => $slug,
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
        Log::info("Импорт производителя {$this->manufacturerId} завершён. Добавлено: {$this->inserted}, пропущено: {$this->skipped}");
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
