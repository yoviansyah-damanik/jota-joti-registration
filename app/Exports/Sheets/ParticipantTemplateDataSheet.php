<?php

namespace App\Exports\Sheets;

use App\Exports\ParticipantTemplateExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantTemplateDataSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithStyles, WithTitle
{
    private const MAX_ROWS = 1000;

    /**
     * Kolom Jenis Kelamin hanya menerima pilihan L atau P (dropdown).
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                for ($row = 2; $row <= self::MAX_ROWS; $row++) {
                    $sheet->getCell('D'.$row)->getDataValidation()
                        ->setType(DataValidation::TYPE_LIST)
                        ->setErrorStyle(DataValidation::STYLE_STOP)
                        ->setAllowBlank(false)
                        ->setShowDropDown(true)
                        ->setShowErrorMessage(true)
                        ->setErrorTitle('Jenis Kelamin tidak valid')
                        ->setError('Pilih L (laki-laki) atau P (perempuan).')
                        ->setFormula1('"L,P"');
                }
            },
        ];
    }

    public function title(): string
    {
        return 'Peserta';
    }

    public function headings(): array
    {
        return ParticipantTemplateExport::HEADINGS;
    }

    /**
     * Satu baris contoh agar format pengisian jelas.
     */
    public function array(): array
    {
        return [
            ['12.345.67.890', 'Contoh Nama Peserta', '2010-08-17', 'L'],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F2E3C6']]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '888888']]],
        ];
    }
}
