<?php

namespace App\Services;

use App\Models\Troop;
use App\Support\CardFpdf;
use App\Support\Rupiah;

/**
 * Membuat PDF bukti pendaftaran (A4) berisi kode pendaftaran dan ringkasan data gudep,
 * agar pendaftar dapat menyimpan kodenya dalam bentuk dokumen.
 */
class RegistrationReceiptPdf
{
    private const PRIMARY = '#5b21b6';

    private const INK = '#1f1a33';

    private const MUTED = '#6b7280';

    public function render(Troop $troop, string $eventName, string $organizationName): string
    {
        $troop->loadCount('participants');

        $pdf = new CardFpdf('P', 'mm', 'A4');
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCreator($eventName);
        $pdf->SetTitle($pdf->latin(__('Registration Receipt').' '.$troop->registration_code), false);
        $pdf->AddPage();

        $this->header($pdf, $eventName, $organizationName);
        $this->codeBox($pdf, $troop);
        $this->details($pdf, $troop);
        $this->notes($pdf, $troop);
        $this->footer($pdf);

        return $pdf->Output('S');
    }

    private function header(CardFpdf $pdf, string $eventName, string $organizationName): void
    {
        $pdf->fillHex(self::PRIMARY);
        $pdf->Rect(0, 0, 210, 48, 'F');

        $pdf->fillHex('#ffffff');
        $pdf->circle(36, 24, 15);
        $pdf->Image(resource_path('images/logo-card.jpg'), 24, 12, 24, 24);

        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetXY(56, 14);
        $pdf->Cell(134, 9, $pdf->latin($eventName));

        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(56, 24);
        $pdf->Cell(134, 6, $pdf->latin($organizationName));

        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(56, 33);
        $pdf->Cell(134, 5, $pdf->latin(mb_strtoupper(__('Registration Receipt'))));
    }

    private function codeBox(CardFpdf $pdf, Troop $troop): void
    {
        $pdf->fillHex('#f3f0fa');
        $pdf->Rect(20, 62, 170, 42, 'F');
        $pdf->fillHex(self::PRIMARY);
        $pdf->Rect(20, 62, 2.5, 42, 'F');

        $pdf->textHex(self::MUTED);
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetXY(20, 68);
        $pdf->Cell(170, 5, $pdf->latin(mb_strtoupper(__('Registration code'))), 0, 0, 'C');

        $pdf->textHex(self::PRIMARY);
        $pdf->SetFont('Courier', 'B', 40);
        $pdf->SetXY(20, 76);
        $pdf->Cell(170, 16, $troop->registration_code, 0, 0, 'C');

        $pdf->textHex(self::MUTED);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetXY(20, 94);
        $pdf->Cell(170, 5, $pdf->latin(__('Use this code to check the registration status.')), 0, 0, 'C');
    }

    private function details(CardFpdf $pdf, Troop $troop): void
    {
        $rows = [
            [__('Troop number'), $troop->number_label],
            [__('Troop name'), $troop->troop_name],
            [__('Unit'), $troop->unit->label()],
            [__('Person in charge'), $troop->pic_name],
            [__('Phone'), $troop->pic_phone],
            [__('Email'), $troop->pic_email ?: '-'],
            [__('Participants'), (string) $troop->participants_count],
            [__('Registration fee'), Rupiah::format($troop->fee_total)],
            [__('Submitted at'), $troop->submitted_at?->translatedFormat('d F Y, H:i') ?? '-'],
            [__('Status'), $troop->status->label()],
        ];

        $y = 116;
        $pdf->textHex(self::INK);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(20, $y);
        $pdf->Cell(170, 7, $pdf->latin(__('Troop Data')));
        $y += 11;

        foreach ($rows as $i => [$label, $value]) {
            if ($i % 2 === 0) {
                $pdf->fillHex('#faf9fc');
                $pdf->Rect(20, $y - 1.5, 170, 9, 'F');
            }

            $pdf->textHex(self::MUTED);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetXY(24, $y);
            $pdf->Cell(52, 6, $pdf->latin($label));

            $pdf->textHex(self::INK);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY(78, $y);
            $pdf->Cell(108, 6, $pdf->latin($value));

            $y += 9;
        }
    }

    private function notes(CardFpdf $pdf, Troop $troop): void
    {
        $y = 232;
        $pdf->textHex(self::INK);
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->SetXY(20, $y);
        $pdf->Cell(170, 6, $pdf->latin(__('Notes')));

        $pdf->textHex(self::MUTED);
        $pdf->SetFont('Helvetica', '', 10);
        $notes = [
            __('Keep this document. The registration code is the only key to check your registration status.'),
            __('The committee will review your data and documents. Check the status regularly with the code above.'),
            __('Participant cards can be printed after your registration is verified and groups are assigned.'),
        ];

        $y += 8;
        foreach ($notes as $note) {
            foreach ($pdf->wrap($pdf->latin($note), 160) as $n => $line) {
                $pdf->SetXY($n === 0 ? 20 : 25, $y);
                $pdf->Cell(5, 5, $n === 0 ? chr(149) : '');
                $pdf->SetXY(25, $y);
                $pdf->Cell(160, 5, $line);
                $y += 5;
            }
            $y += 1.5;
        }

        $pdf->textHex(self::PRIMARY);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY(20, $y + 3);
        $pdf->Cell(170, 6, route('status', ['code' => $troop->registration_code]), 0, 0, 'L', false, route('status', ['code' => $troop->registration_code]));
    }

    private function footer(CardFpdf $pdf): void
    {
        $pdf->fillHex(self::PRIMARY);
        $pdf->Rect(0, 287, 210, 10, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(0, 290);
        $pdf->Cell(210, 4, $pdf->latin(__('Generated on :date', ['date' => now()->translatedFormat('d F Y, H:i')])), 0, 0, 'C');
    }
}
