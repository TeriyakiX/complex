<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\Marketplace\MarketplacesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MarketplaceImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $drawings = $sheet->getDrawingCollection();

        $drawingsByCoordinate = [];
        foreach ($drawings as $drawing) {
            $drawingsByCoordinate[$drawing->getCoordinates()] = $drawing;
        }

        Excel::import(new MarketplacesImport($drawingsByCoordinate), $request->file('file'));

        return response()->json(['message' => 'Импорт маркетплейсов успешно завершен']);
    }
}
