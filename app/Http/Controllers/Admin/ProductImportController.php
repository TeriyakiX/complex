<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\ProductImport;
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
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $file = $request->file('file');
            Log::info('Начало импорта файла: ' . $file->getClientOriginalName());

            $import = new ProductImport($file->getRealPath());
            Excel::import($import, $file);

            foreach ($import->sheets() as $sheetName => $sheetImport) {
                if (method_exists($sheetImport, 'finalize')) {
                    $sheetImport->finalize();
                    Log::info("Лист '{$sheetName}' обработан");
                }
            }

            return response()->json([
                'message' => 'Импорт успешно выполнен'
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка импорта: ' . $e->getMessage());

            return response()->json([
                'message' => 'Произошла ошибка при импорте файла',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
