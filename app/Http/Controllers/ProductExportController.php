<?php

namespace App\Http\Controllers;

use App\Jobs\ExportProductsJob;
use App\Models\ExportStatus;

class ProductExportController extends Controller
{
    public function export()
    {
        $fileName = 'products_' . uniqid() . '.xlsx';

        $status = ExportStatus::create([
            'file_name' => $fileName,
            'status' => 'pending',
        ]);

        ExportProductsJob::dispatch($status->id);

        return response()->json([
            'message' => 'Экспорт запущен',
            'export_id' => $status->id,
        ], 202);
    }

    public function exportStatus($id)
    {
        $status = ExportStatus::find($id);

        if (!$status) {
            return response()->json(['error' => 'Экспорт не найден'], 404);
        }

        return response()->json([
            'status' => $status->status,
            'message' => $status->message,
            'file' => $status->status === 'done'
                ? url('storage/exports/' . $status->file_name)
                : null,
        ]);
    }
}
