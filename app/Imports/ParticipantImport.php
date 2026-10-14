<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Import;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Hanya membaca sheet pertama; validasi dilakukan di ParticipantSheetParser.
 */
class ParticipantImport implements Import, SkipsEmptyRows, WithCalculatedFormulas, WithHeadingRow
{
    public const COLUMNS = ['no_kta', 'nama_lengkap', 'tanggal_lahir', 'jenis_kelamin'];

    /**
     * Nomor KTA pada baris contoh template, diabaikan saat impor.
     */
    public const EXAMPLE_KTA = '12.345.67.890';
}
