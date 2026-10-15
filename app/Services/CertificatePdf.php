<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Troop;
use App\Support\CardFpdf;
use Illuminate\Support\Collection;

/**
 * Membuat PDF e-sertifikat (A4 landscape): satu penerima = satu halaman.
 * Mendukung template gambar kustom dan penataan posisi teks.
 */
class CertificatePdf
{
    private const WIDTH = 297;

    private const HEIGHT = 210;

    private const PRIMARY = '#5b21b6';

    private const ACCENT = '#f5a524';

    private const INK = '#1f1a33';

    private const MUTED = '#6b7280';

    public function __construct(private SettingService $settings) {}

    /**
     * @param  Collection<int, Participant>  $participants
     */
    public function participants(Troop $troop, Collection $participants): string
    {
        $hasCustom = $this->settings->hasCustomParticipantTemplate();
        $templatePath = $this->settings->certificateParticipantTemplatePath();
        $layout = $this->settings->certificateParticipantLayout();

        $pdf = $this->initPdf(__('Participant Certificate').' '.$troop->number_label);

        foreach ($participants as $p) {
            $pdf->AddPage();
            $idText = (string) ($p->attendance_code ?? $p->kta_number);

            if ($hasCustom && $templatePath) {
                $this->pageCustomParticipant($pdf, $templatePath, $layout, $p->full_name, $idText);
            } else {
                $description = __('from :troop (:name) for taking part as a participant in :event organized by :org.', [
                    'troop' => __('Troop').' '.$troop->number_label,
                    'name' => $troop->troop_name,
                    'event' => $this->settings->get('event_name'),
                    'org' => $this->settings->get('organization_name'),
                ]);
                $this->pageDefault($pdf, $p->full_name, $description, $idText);
            }
        }

        return $pdf->Output('S');
    }

    public function troop(Troop $troop): string
    {
        $hasCustom = $this->settings->hasCustomTroopTemplate();
        $templatePath = $this->settings->certificateTroopTemplatePath();
        $layout = $this->settings->certificateTroopLayout();

        $pdf = $this->initPdf(__('Troop Certificate').' '.$troop->number_label);
        $pdf->AddPage();

        $troopNumber = __('Troop').' '.$troop->number_label;
        $troopName = $troop->troop_name;

        if ($hasCustom && $templatePath) {
            $this->pageCustomTroop($pdf, $templatePath, $layout, $troopName, $troopNumber);
        } else {
            $description = __('for its participation in :event organized by :org.', [
                'event' => $this->settings->get('event_name'),
                'org' => $this->settings->get('organization_name'),
            ]);
            $this->pageDefault($pdf, $troopNumber.' - '.$troopName, $description, $troop->registration_code);
        }

        return $pdf->Output('S');
    }

    /**
     * Preview contoh sertifikat (peserta atau gudep).
     */
    public function previewSample(string $type): string
    {
        $type = $type === 'troop' ? 'troop' : 'participant';
        $pdf = $this->initPdf('Preview Sertifikat '.ucfirst($type));
        $pdf->AddPage();

        if ($type === 'participant') {
            $templatePath = $this->settings->certificateParticipantTemplatePath();
            $layout = $this->settings->certificateParticipantLayout();

            if ($templatePath) {
                $this->pageCustomParticipant($pdf, $templatePath, $layout, 'Nama Lengkap Peserta', 'JJID36064');
            } else {
                $description = __('from :troop (:name) for taking part as a participant in :event organized by :org.', [
                    'troop' => __('Troop').' 01.001 - 01.002',
                    'name' => 'Gugusdepan Pangkalan Contoh',
                    'event' => $this->settings->get('event_name'),
                    'org' => $this->settings->get('organization_name'),
                ]);
                $this->pageDefault($pdf, 'Nama Lengkap Peserta', $description, 'JJID36064');
            }
        } else {
            $templatePath = $this->settings->certificateTroopTemplatePath();
            $layout = $this->settings->certificateTroopLayout();

            if ($templatePath) {
                $this->pageCustomTroop($pdf, $templatePath, $layout, 'Gugusdepan Pangkalan Contoh', '01.001 - 01.002');
            } else {
                $description = __('for its participation in :event organized by :org.', [
                    'event' => $this->settings->get('event_name'),
                    'org' => $this->settings->get('organization_name'),
                ]);
                $this->pageDefault($pdf, __('Troop').' 01.001 - 01.002 - Gugusdepan Pangkalan Contoh', $description, 'JJ-SAMPLE');
            }
        }

        return $pdf->Output('S');
    }

    private function initPdf(string $title): CardFpdf
    {
        $pdf = new CardFpdf('L', 'mm', 'A4');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCreator((string) $this->settings->get('event_name'));
        $pdf->SetTitle($pdf->latin($title), false);

        return $pdf;
    }

    /**
     * @param  array<string, array{x: float, y: float, font_size: float, color: string, align: string, bold: bool}>  $layout
     */
    private function pageCustomParticipant(CardFpdf $pdf, string $templatePath, array $layout, string $name, string $id): void
    {
        $pdf->Image($templatePath, 0, 0, self::WIDTH, self::HEIGHT);

        if (isset($layout['full_name'])) {
            $this->renderFieldText($pdf, $name, $layout['full_name']);
        }

        if (isset($layout['attendance_code'])) {
            $this->renderFieldText($pdf, $id, $layout['attendance_code']);
        }
    }

    /**
     * @param  array<string, array{x: float, y: float, font_size: float, color: string, align: string, bold: bool}>  $layout
     */
    private function pageCustomTroop(CardFpdf $pdf, string $templatePath, array $layout, string $troopName, string $troopNumber): void
    {
        $pdf->Image($templatePath, 0, 0, self::WIDTH, self::HEIGHT);

        if (isset($layout['troop_name'])) {
            $this->renderFieldText($pdf, $troopName, $layout['troop_name']);
        }

        if (isset($layout['troop_number'])) {
            $this->renderFieldText($pdf, $troopNumber, $layout['troop_number']);
        }
    }

    /**
     * Render teks pada koordinat dan tipografi kustom.
     *
     * @param  array{x: float, y: float, font_size: float, color: string, align: string, bold: bool}  $cfg
     */
    private function renderFieldText(CardFpdf $pdf, string $text, array $cfg): void
    {
        $fontSize = (float) ($cfg['font_size'] ?? 20);
        $style = ! empty($cfg['bold']) ? 'B' : '';
        $pdf->SetFont('Helvetica', $style, $fontSize);

        $color = $cfg['color'] ?? '#1f1a33';
        $pdf->textHex($color);

        $xPercent = (float) ($cfg['x'] ?? 50);
        $yPercent = (float) ($cfg['y'] ?? 50);

        $xMm = ($xPercent / 100) * self::WIDTH;
        $yMm = ($yPercent / 100) * self::HEIGHT;

        $align = strtolower($cfg['align'] ?? 'center');
        $latinText = $pdf->latin($text);
        $lineHeight = $fontSize * 0.45;

        // Rentang kotak teks 240mm untuk fleksibilitas perataan
        $boxWidth = 240;

        if ($align === 'center') {
            $pdf->SetXY($xMm - ($boxWidth / 2), $yMm - ($lineHeight / 2));
            $pdf->Cell($boxWidth, $lineHeight, $latinText, 0, 0, 'C');
        } elseif ($align === 'right') {
            $pdf->SetXY($xMm - $boxWidth, $yMm - ($lineHeight / 2));
            $pdf->Cell($boxWidth, $lineHeight, $latinText, 0, 0, 'R');
        } else {
            $pdf->SetXY($xMm, $yMm - ($lineHeight / 2));
            $pdf->Cell($boxWidth, $lineHeight, $latinText, 0, 0, 'L');
        }
    }

    /**
     * Tampilan default sertifikat bawaan aplikasi bila belum ada template gambar kustom.
     */
    private function pageDefault(CardFpdf $pdf, string $recipient, string $description, string $id): void
    {
        $this->frame($pdf);

        $pdf->Image(resource_path('images/logo-card.jpg'), (self::WIDTH - 26) / 2, 18, 26, 26);

        $pdf->textHex(self::PRIMARY);
        $pdf->SetFont('Helvetica', 'B', 34);
        $this->center($pdf, 48, 14, mb_strtoupper(__('Certificate')), 34);

        $pdf->textHex(self::MUTED);
        $this->center($pdf, 63, 6, __('is proudly presented to'), 13, 'I');

        $width = 235;
        $size = $pdf->fitFontSize($pdf->latin($recipient), $width, 1, 34, 16);
        $pdf->textHex(self::INK);
        $this->center($pdf, 74, 15, $recipient, $size, 'B');

        $pdf->drawHex(self::ACCENT);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(70, 92, self::WIDTH - 70, 92);

        $pdf->textHex(self::INK);
        $pdf->SetFont('Helvetica', '', 13);
        $y = 98;
        foreach ($pdf->wrap($pdf->latin($description), 200) as $line) {
            $pdf->SetXY(0, $y);
            $pdf->Cell(self::WIDTH, 7, $line, 0, 0, 'C');
            $y += 7;
        }

        $this->signature($pdf);

        $pdf->textHex(self::MUTED);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(20, self::HEIGHT - 17);
        $pdf->Cell(90, 4, $pdf->latin('ID: '.$id));
    }

    private function frame(CardFpdf $pdf): void
    {
        $pdf->fillHex(self::PRIMARY);
        $pdf->Rect(0, 0, self::WIDTH, self::HEIGHT, 'F');
        $pdf->fillHex('#ffffff');
        $pdf->Rect(6, 6, self::WIDTH - 12, self::HEIGHT - 12, 'F');

        $pdf->drawHex(self::ACCENT);
        $pdf->SetLineWidth(0.6);
        $pdf->Rect(10, 10, self::WIDTH - 20, self::HEIGHT - 20);
    }

    private function signature(CardFpdf $pdf): void
    {
        $x = self::WIDTH - 110;
        $date = $this->settings->get('event_completed_at');
        $date = $date ? \Illuminate\Support\Carbon::parse($date) : now();

        $pdf->textHex(self::INK);
        $pdf->SetFont('Helvetica', '', 11);
        $pdf->SetXY($x, 132);
        $pdf->Cell(90, 6, $pdf->latin($this->settings->get('certificate_city').', '.$date->translatedFormat('d F Y')), 0, 0, 'C');

        $pdf->SetXY($x, 139);
        $pdf->Cell(90, 6, $pdf->latin((string) $this->settings->get('certificate_signer_title')), 0, 0, 'C');

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY($x, 170);
        $pdf->Cell(90, 6, $pdf->latin((string) $this->settings->get('certificate_signer_name')), 0, 0, 'C');

        $pdf->drawHex(self::INK);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($x + 5, 177, $x + 85, 177);
    }

    /**
     * Teks satu baris di tengah halaman pada posisi y tertentu.
     */
    private function center(CardFpdf $pdf, float $y, float $height, string $text, float $size, string $style = 'B'): void
    {
        $pdf->SetFont('Helvetica', $style, $size);
        $pdf->SetXY(0, $y);
        $pdf->Cell(self::WIDTH, $height, $pdf->latin($text), 0, 0, 'C');
    }
}

