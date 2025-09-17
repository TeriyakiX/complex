<?php

namespace App\Exports;

use App\Models\Manufacturer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ManufacturersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnWidths, WithStyles, WithDrawings
{
    protected $manufacturers;

    public function __construct()
    {
        $this->manufacturers = Manufacturer::all(['name', 'description', 'image']);
    }

    public function collection()
    {
        return $this->manufacturers;
    }

    public function headings(): array
    {
        return ['Name', 'Description', 'Image'];
    }

    public function map($manufacturer): array
    {
        return [
            $manufacturer->name,
            $manufacturer->description ?? '',
            '' // изображение добавляется через WithDrawings
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 50,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Заголовок жирным
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // Вся колонка B — выравнивание и перенос текста
        $sheet->getStyle('B:B')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }

    public function drawings()
    {
        $drawings = [];
        $row = 2; // начинаем со второй строки

        foreach ($this->manufacturers as $manufacturer) {
            if ($manufacturer->image && file_exists(storage_path('app/public/' . $manufacturer->image))) {
                $drawing = new Drawing();
                $drawing->setName($manufacturer->name);
                $drawing->setDescription($manufacturer->description ?? '');
                $drawing->setPath(storage_path('app/public/' . $manufacturer->image));
                $drawing->setHeight(60); // высота изображения
                $drawing->setCoordinates('C' . $row);

                $drawings[] = $drawing;
            }
            $row++;
        }

        return $drawings;
    }
}
