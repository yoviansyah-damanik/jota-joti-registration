<?php

namespace App\Console\Commands;

use Database\Seeders\Support\DemoDocuments;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:samples {--path=contoh-upload : Folder tujuan relatif terhadap root project}')]
#[Description('Membuat file contoh (Excel, surat tugas, bukti bayar) untuk mencoba formulir pendaftaran')]
class MakeDemoSamples extends Command
{
    public function handle(): int
    {
        $dir = base_path($this->option('path'));
        @mkdir($dir, 0777, true);

        $valid = collect(range(1, 12))->map(fn (int $i) => [
            sprintf('9999.%03d', $i),
            ['Ahmad Fauzi Harahap', 'Siti Nurhaliza Siregar', 'Rizki Ramadhan Lubis', 'Aisyah Putri Nasution', 'Muhammad Iqbal Daulay', 'Nadia Salsabila Pulungan'][($i - 1) % 6],
            sprintf('201%d-%02d-%02d', $i % 5, ($i % 12) + 1, ($i % 27) + 1),
            $i % 2 ? 'L' : 'P',
        ])->all();

        // tiap baris memuat satu jenis kesalahan agar pesan validasi bisa dicoba
        $invalid = [
            ['9998.001', 'Nomor KTA Ganda (baris pertama)', '2011-05-10', 'L'],
            ['9998.001', 'Nomor KTA Ganda (baris kedua)', '2011-06-11', 'P'],
            ['', 'KTA Kosong', '2011-07-12', 'L'],
            ['9998.004', '', '2011-08-13', 'P'],
            ['9998.005', 'Tanggal Lahir Salah', '31-31-2011', 'L'],
            ['9998.006', 'Jenis Kelamin Salah', '2011-09-14', 'X'],
            ['9998.007', 'Tanggal Lahir Masa Depan', '2099-01-01', 'P'],
            ['9998.008', 'Peserta Valid', '2010-03-04', 'P'],
        ];

        $files = [
            'peserta-valid.xlsx' => DemoDocuments::participantsXlsx($valid),
            'peserta-berisi-kesalahan.xlsx' => DemoDocuments::participantsXlsx($invalid),
            'peserta-terlalu-sedikit.xlsx' => DemoDocuments::participantsXlsx(array_slice($valid, 0, 3)),
            'surat-tugas.pdf' => DemoDocuments::pdf(['SURAT TUGAS', 'Gugus Depan 99-001 & 99-002', 'SMP Negeri Contoh', '', 'Pembina Gugus Depan,', 'Kak Contoh, S.Pd.']),
            'bukti-transfer.png' => DemoDocuments::receiptPng(['Tanggal  : '.now()->format('d/m/Y H:i'), 'Dari     : Kak Contoh', 'Ke       : Panitia JOTA JOTI Kwarcab Psp', 'Jumlah   : Rp 50.000']),
        ];

        foreach ($files as $name => $content) {
            file_put_contents("{$dir}/{$name}", $content);
            $this->line("  {$name}");
        }

        $this->info('File contoh dibuat di '.$dir);

        return self::SUCCESS;
    }
}
