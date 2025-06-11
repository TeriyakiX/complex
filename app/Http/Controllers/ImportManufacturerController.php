<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\ManufacturersImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportManufacturerController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');

        // Загружаем файл через PhpSpreadsheet, чтобы получить рисунки с координатами
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();

        $drawingsByCoordinate = [];
        foreach ($sheet->getDrawingCollection() as $drawing) {
            $drawingsByCoordinate[$drawing->getCoordinates()] = $drawing;
        }

        // Запускаем импорт и передаем массив с рисунками
        Excel::import(new ManufacturersImport($drawingsByCoordinate), $file);

        return response()->json(['message' => 'Импорт производителей выполнен']);
    }
}
