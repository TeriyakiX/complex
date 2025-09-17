<?php

namespace App\Http\Controllers;

use App\Exports\ManufacturersExport;
use Maatwebsite\Excel\Facades\Excel;

class ManufacturerExportController extends Controller
{
    public function export()
    {
        $fileName = 'manufacturers_' . uniqid() . '.xlsx';

        Excel::store(new ManufacturersExport(), "exports/{$fileName}");

        return response()->json([
            'message' => 'Экспорт завершён',
            'file' => url("storage/exports/{$fileName}"),
        ]);
    }
}
