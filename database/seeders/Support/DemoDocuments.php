<?php

namespace Database\Seeders\Support;

use App\Exports\ParticipantTemplateExport;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Pembuat file contoh (PDF, PNG, XLSX) untuk data demo dan uji coba formulir.
 */
class DemoDocuments
{
    /**
     * PDF satu halaman berisi baris teks sederhana.
     *
     * @param  array<int, string>  $lines
     */
    public static function pdf(array $lines): string
    {
        $text = "BT /F1 13 Tf 60 780 Td 18 TL\n";
        foreach ($lines as $line) {
            $text .= '('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line).") Tj T*\n";
        }
        $text .= 'ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($text)." >>\nstream\n{$text}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
    }

    /**
     * Gambar PNG menyerupai struk bukti transfer.
     *
     * @param  array<int, string>  $lines
     */
    public static function receiptPng(array $lines): string
    {
        $image = imagecreatetruecolor(600, 360);
        imagefill($image, 0, 0, imagecolorallocate($image, 250, 250, 247));
        imagefilledrectangle($image, 0, 0, 600, 60, imagecolorallocate($image, 20, 70, 150));
        imagestring($image, 5, 24, 22, 'BUKTI TRANSFER', imagecolorallocate($image, 255, 255, 255));

        $dark = imagecolorallocate($image, 40, 40, 40);
        foreach ($lines as $i => $line) {
            imagestring($image, 4, 24, 90 + $i * 32, $line, $dark);
        }

        ob_start();
        imagepng($image);

        return (string) ob_get_clean();
    }

    /**
     * File Excel peserta sesuai template.
     *
     * @param  array<int, array>  $rows  [kta, nama, tanggal lahir, L/P]
     */
    public static function participantsXlsx(array $rows): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Peserta');
        $sheet->fromArray(ParticipantTemplateExport::HEADINGS, null, 'A1');
        foreach ($rows as $i => $row) {
            foreach (array_values($row) as $col => $value) {
                // semua kolom disimpan sebagai teks agar KTA/tanggal tidak berubah format
                $sheet->setCellValueExplicit([$col + 1, $i + 2], (string) $value, DataType::TYPE_STRING);
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'demo');
        (new Xlsx($spreadsheet))->save($path);
        $content = (string) file_get_contents($path);
        @unlink($path);

        return $content;
    }
}
