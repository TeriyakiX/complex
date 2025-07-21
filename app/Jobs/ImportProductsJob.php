<?php

namespace App\Jobs;

use App\Imports\ProductImport;
use App\Jobs\Middleware\AddUuidToJob;
use App\Models\ImportStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ImportProductsJob implements ShouldQueue
{

    public function middleware()
    {
        return [new AddUuidToJob()];
    }

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    protected int $importStatusId;

    public function __construct(string $filePath, int $importStatusId)
    {
        $this->filePath = $filePath;
        $this->importStatusId = $importStatusId;
    }

    public function handle(): void
    {
        $status = ImportStatus::find($this->importStatusId);
        if ($status) {
            $status->update(['status' => 'processing']);
        }

        try {
            Log::info("Start import from: {$this->filePath}");
            Excel::import(new ProductImport($this->filePath), $this->filePath);
            Log::info("Import complete from: {$this->filePath}");

            if ($status) {
                $status->update([
                    'status' => 'done',
                    'message' => 'Импорт успешно завершён',
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Import failed: " . $e->getMessage());
            if ($status) {
                $status->update([
                    'status' => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }
}
