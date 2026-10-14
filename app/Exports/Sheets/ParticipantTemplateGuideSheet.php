<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ParticipantTemplateGuideSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Petunjuk';
    }

    public function array(): array
    {
        return [
            ['Kolom', 'Keterangan'],
            ['No KTA', 'Nomor Kartu Tanda Anggota / NTA Pramuka. Opsional (kosongkan jika tidak ada), tidak boleh sama antar peserta jika diisi.'],
            ['Nama Lengkap', 'Nama lengkap peserta sesuai KTA.'],
            ['Tanggal Lahir', 'Format TTTT-BB-HH (contoh 2010-08-17) atau HH/BB/TTTT (contoh 17/08/2010).'],
            ['Jenis Kelamin', 'Pilih L (laki-laki) atau P (perempuan) dari dropdown. Nilai lain tidak diterima.'],
            ['', ''],
            ['Catatan', 'Hapus baris contoh pada sheet "Peserta" sebelum mengunggah. Jangan mengubah judul kolom.'],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]], 'A' => ['font' => ['bold' => true]]];
    }
}
