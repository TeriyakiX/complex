<?php

namespace App\Jobs;

use App\Exports\ProductExport;
use App\Models\ExportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $exportStatusId;

    public function __construct(int $exportStatusId)
    {
        $this->exportStatusId = $exportStatusId;
    }

    public function handle(): void
    {
        $status = ExportStatus::find($this->exportStatusId);
        if (!$status) {
            return;
        }

        $status->update(['status' => 'processing']);

        try {
            $fileName = $status->file_name;
            $path = "exports/{$fileName}";

            Excel::store(new ProductExport(), $path);

            $status->update([
                'status' => 'done',
                'message' => "Файл готов: {$path}",
            ]);

            Log::info("Экспорт завершён: {$path}");
        } catch (\Throwable $e) {
            $status->update([
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);
            Log::error("Ошибка экспорта: " . $e->getMessage());
        }
    }
}
