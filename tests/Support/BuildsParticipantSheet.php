<?php

namespace Tests\Support;

use App\Exports\ParticipantTemplateExport;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait BuildsParticipantSheet
{
    /**
     * Membuat file .xlsx peserta untuk pengujian.
     *
     * @param  array<int, array>  $rows
     */
    protected function participantSheet(array $rows, ?array $headings = null): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([$headings ?? ParticipantTemplateExport::HEADINGS, ...$rows], null, 'A1', true);

        $path = tempnam(sys_get_temp_dir(), 'peserta').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return UploadedFile::fake()->createWithContent('peserta.xlsx', file_get_contents($path));
    }

    /**
     * @return array<int, array>
     */
    protected function validRows(int $count, string $prefix = 'KTA'): array
    {
        return collect(range(1, $count))
            ->map(fn ($i) => ["{$prefix}-{$i}", "Peserta {$i}", '2010-01-'.str_pad((string) (($i % 28) + 1), 2, '0', STR_PAD_LEFT), $i % 2 ? 'L' : 'P'])
            ->all();
    }
}
