<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Troop;
use App\Support\CardFpdf;
use App\Support\GroupColor;
use App\Support\QrCode;
use Illuminate\Support\Collection;

/**
 * Membuat PDF kartu peserta: satu peserta = satu halaman B3 vertikal (353 × 500 mm).
 * Desain color-block modern (blok warna grup, kartu nama melayang) mengikuti pratinjau HTML (resources/views/print/participant-cards.blade.php);
 * ubah keduanya bersamaan.
 */
class ParticipantCardPdf
{
    public const WIDTH = 353;

    public const HEIGHT = 500;

    private const FOOT = 6;

    private const QR = 172;

    private const QR_PAD = 10;

    private const PT = 2.8346;

    /**
     * @param  Collection<int, Participant>  $participants
     * @return string isi berkas PDF
     */
    public function render(Troop $troop, Collection $participants, string $eventName, string $organizationName): string
    {
        $pdf = new CardFpdf('P', 'mm', [self::WIDTH, self::HEIGHT]);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCreator($eventName);
        $pdf->SetTitle($pdf->latin(__('Participant Cards').' '.$troop->number_label), false);

        foreach ($participants as $participant) {
            $this->page($pdf, $troop, $participant, $eventName, $organizationName);
        }

        return $pdf->Output('S');
    }

    private function page(CardFpdf $pdf, Troop $troop, Participant $participant, string $eventName, string $organizationName): void
    {
        $color = GroupColor::for($participant->group_number);
        $accent = $color['background'];
        $center = self::WIDTH / 2;
        $pdf->AddPage();

        // ---- latar lembut
        $pdf->fillHex('#f7f7f5');
        $pdf->Rect(0, 0, self::WIDTH, self::HEIGHT, 'F');

        // ---- blok warna grup + lingkaran dekoratif
        $pdf->fillHex($accent);
        $pdf->Rect(0, 0, self::WIDTH, 140, 'F');
        $pdf->fillHex(GroupColor::tint($participant->group_number, 0.1));
        $pdf->circle(318, 20, 100);

        // ---- logo + nama kegiatan (kiri)
        $pdf->fillHex('#ffffff');
        $pdf->circle(52, 48, 28);
        $pdf->Image(resource_path('images/logo-card.jpg'), 33, 29, 38, 38);

        $width = 190;
        // dua baris: nama kegiatan (besar) dan penyelenggara (lebih kecil), masing-masing satu baris
        $eventSize = $pdf->fitFontSize($pdf->latin($eventName), $width, 1, 36, 14);
        $orgSize = $pdf->fitFontSize($pdf->latin($organizationName), $width, 1, $eventSize * 0.75, 10);
        $pdf->textHex($color['text']);

        $eventHeight = $eventSize / self::PT * 1.15;
        $orgHeight = $orgSize / self::PT * 1.15;
        $y = 101 - ($eventHeight + $orgHeight) / 2;

        $pdf->SetFont('Helvetica', 'B', $eventSize);
        $pdf->SetXY(24, $y);
        $pdf->Cell($width, $eventHeight, $pdf->latin($eventName), 0, 0, 'L');

        $pdf->SetFont('Helvetica', 'B', $orgSize);
        $pdf->SetXY(24, $y + $eventHeight);
        $pdf->Cell($width, $orgHeight, $pdf->latin($organizationName), 0, 0, 'L');

        // ---- nomor grup: cakram putih ber-cincin sebagai sorotan (kanan)
        $pdf->fillHex(GroupColor::tint($participant->group_number, 0.25));
        $pdf->circle(272, 78, 50);
        $pdf->fillHex('#ffffff');
        $pdf->circle(272, 78, 44);

        $number = $participant->group_number ? (string) $participant->group_number : '-';
        $size = match (strlen($number)) {
            1 => 150,
            2 => 120,
            default => 90,
        };
        $pdf->textHex($accent);
        $pdf->SetFont('Helvetica', 'B', $size);
        $pdf->Text(272 - $pdf->GetStringWidth($number) / 2, 78 + 0.72 * $size / self::PT / 2, $number);

        // ---- kartu nama melayang
        $pdf->fillHex('#e4e4e0');
        $pdf->Rect(24, 124.5, 305, 88, 'F');
        $pdf->fillHex('#ffffff');
        $pdf->Rect(24, 122, 305, 88, 'F');

        $pdf->textHex('#111827');
        $name = $pdf->latin($participant->full_name);
        $size = $pdf->fitFontSize($name, 285, 2, 46, 26);
        $pdf->SetFont('Helvetica', 'B', $size);
        $lineHeight = $size / self::PT * 1.08;
        $lines = $pdf->wrap($name, 285);
        $troopLines = $pdf->wrap($pdf->latin($troop->troop_name), 285);

        // isi kartu (nama + gugusdepan) diratakan ke tengah secara vertikal
        $contentHeight = count($lines) * $lineHeight + 3 + 9.5 + count($troopLines) * 8.5;
        $y = 122 + (88 - $contentHeight) / 2;
        foreach ($lines as $line) {
            $pdf->SetXY(34, $y);
            $pdf->Cell(285, $lineHeight, $line, 0, 0, 'C');
            $y += $lineHeight;
        }

        $y += 3;
        $pdf->textHex($accent);
        $pdf->SetFont('Helvetica', 'B', 24);
        $pdf->SetXY(34, $y);
        $pdf->Cell(285, 9.5, $pdf->latin($troop->number_label), 0, 0, 'C');
        $y += 9.5;

        $pdf->textHex('#6b7280');
        $pdf->SetFont('Helvetica', '', 22);
        foreach ($troopLines as $line) {
            $pdf->SetXY(34, $y);
            $pdf->Cell(285, 8.5, $line, 0, 0, 'C');
            $y += 8.5;
        }

        // ---- QR code (vektor) pada kartu putih melayang
        $pdf->textHex('#9ca3af');
        $pdf->SetFont('Helvetica', 'B', 15);
        $label = $pdf->latin(mb_strtoupper(__('Scan at the attendance desk')));
        $pdf->Text($center - $pdf->GetStringWidth($label) / 2, 240, $label);

        $frame = self::QR + 2 * self::QR_PAD;
        $frameX = ($pdf->GetPageWidth() - $frame) / 2;
        $frameY = 250;

        $pdf->fillHex('#e4e4e0');
        $pdf->Rect($frameX, $frameY + 2.5, $frame, $frame, 'F');
        $pdf->fillHex('#ffffff');
        $pdf->Rect($frameX, $frameY, $frame, $frame, 'F');
        $pdf->brackets($frameX, $frameY, $frame, $frame, 14, 1.8, $accent);
        $this->qr($pdf, $participant->attendance_code, $frameX + self::QR_PAD, $frameY + self::QR_PAD);

        // ---- ID peserta & pita bawah
        $pdf->textHex('#111827');
        $pdf->SetFont('Courier', 'B', 34);
        $code = (string) $participant->attendance_code;
        $pdf->Text($center - $pdf->GetStringWidth($code) / 2, 474, $code);

        $pdf->fillHex($accent);
        $pdf->Rect(0, self::HEIGHT - self::FOOT, self::WIDTH, self::FOOT, 'F');
    }
    /**
     * Menggambar QR code sebagai persegi vektor; modul sebaris digabung agar tanpa garis celah.
     */
    private function qr(CardFpdf $pdf, string $data, float $x, float $y): void
    {
        $matrix = QrCode::matrix($data);
        $module = self::QR / count($matrix);
        $pdf->fillHex('#000000');

        foreach ($matrix as $row => $cells) {
            $col = 0;
            $count = count($cells);

            while ($col < $count) {
                if (! $cells[$col]) {
                    $col++;

                    continue;
                }

                $start = $col;
                while ($col < $count && $cells[$col]) {
                    $col++;
                }

                $pdf->Rect($x + $start * $module, $y + $row * $module, ($col - $start) * $module + 0.05, $module + 0.05, 'F');
            }
        }
    }
}
