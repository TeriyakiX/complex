<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
use App\Jobs\ImportProductsJob;
use App\Models\ImportStatus;
use App\Models\Product;
use App\Models\Manufacturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportController extends Controller
{
    public function import(Request $request)
    {

        Log::info('QUEUE_CONNECTION: ' . config('queue.default'));
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');
            Log::info('Запуск импорта файла: ' . $file->getClientOriginalName());

            $fileName = uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('imports', $fileName);

            $importStatus = ImportStatus::create([
                'file_name' => $fileName,
                'status' => 'pending',
            ]);

            ImportProductsJob::dispatch(storage_path("app/{$path}"), $importStatus->id);

            return response()->json([
                'message' => 'Импорт запущен',
                'import_id' => $importStatus->id,
            ], 202);

        } catch (\Throwable $e) {
            Log::error('Ошибка запуска импорта: ' . $e->getMessage());

            return response()->json([
                'message' => 'Не удалось запустить импорт',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function importStatus($id)
    {
        $status = ImportStatus::find($id);

        if (!$status) {
            return response()->json(['error' => 'Импорт не найден'], 404);
        }

        return response()->json([
            'status' => $status->status,
            'message' => $status->message,
        ]);
    }
}
