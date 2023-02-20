<?php

namespace App\Exports;


use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BaseExport implements WithStyles, WithDefaultStyles
{

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the Head title row as bold text.
            1 => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000')
                    )
                ],
                'font' => [
                    'bold' => true,
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => Color::COLOR_CYAN],
                ],
            ],

            //            // Styling a specific cell by coordinate.
            //            'B2' => ['font' => ['italic' => true]],
            //
            //            // Styling an entire column.
            //            'C'  => ['font' => ['size' => 16]],
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {
        // Configure the default styles
        //        return $defaultStyle->getFill()->setFillType(Fill::FILL_SOLID);

        // Or return the styles array
        return [
            'font' => array(
                'bold' => false,
                'color' => array('rgb' => '000000'),
                'size' => 11,
                'name' => 'Arial'
            ),
            'alignment' => array(
                'vertical' => Alignment::VERTICAL_CENTER,
            ),
            //            'fill' => [
            //                'fillType'   => Fill::FILL_SOLID,
            //                'startColor' => ['argb' => Color::COLOR_WHITE],
            //            ],
        ];
    }
}
