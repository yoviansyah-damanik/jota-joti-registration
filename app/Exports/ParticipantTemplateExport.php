<?php

namespace App\Exports;

use App\Exports\Sheets\ParticipantTemplateDataSheet;
use App\Exports\Sheets\ParticipantTemplateGuideSheet;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ParticipantTemplateExport implements Export, WithMultipleSheets
{
    /**
     * Judul kolom template (harus sama dengan yang dibaca ParticipantImport).
     */
    public const HEADINGS = ['No KTA', 'Nama Lengkap', 'Tanggal Lahir', 'Jenis Kelamin'];

    public function sheets(): array
    {
        return [
            new ParticipantTemplateDataSheet,
            new ParticipantTemplateGuideSheet,
        ];
    }
}
