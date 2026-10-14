<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Troop;
use App\Support\CardFpdf;
use Illuminate\Support\Collection;

/**
 * Membuat PDF e-sertifikat (A4 landscape): satu penerima = satu halaman.
 * Penerima berupa peserta atau gudep; isi kalimat menyesuaikan jenisnya.
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
        return $this->build($participants->map(fn (Participant $p) => [
            'recipient' => $p->full_name,
            'description' => __('from :troop (:name) for taking part as a participant in :event organized by :org.', [
                'troop' => __('Troop').' '.$troop->number_label,
                'name' => $troop->troop_name,
                'event' => $this->settings->get('event_name'),
                'org' => $this->settings->get('organization_name'),
            ]),
            'id' => $p->attendance_code ?? $p->kta_number,
        ])->all(), __('Participant Certificate').' '.$troop->number_label);
    }

    public function troop(Troop $troop): string
    {
        return $this->build([[
            'recipient' => __('Troop').' '.$troop->number_label.' - '.$troop->troop_name,
            'description' => __('for its participation in :event organized by :org.', [
                'event' => $this->settings->get('event_name'),
                'org' => $this->settings->get('organization_name'),
            ]),
            'id' => $troop->registration_code,
        ]], __('Troop Certificate').' '.$troop->number_label);
    }

    /**
     * @param  array<int, array{recipient: string, description: string, id: string}>  $items
     */
    private function build(array $items, string $title): string
    {
        $pdf = new CardFpdf('L', 'mm', 'A4');
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCreator((string) $this->settings->get('event_name'));
        $pdf->SetTitle($pdf->latin($title), false);

        foreach ($items as $item) {
            $this->page($pdf, $item);
        }

        return $pdf->Output('S');
    }

    /**
     * @param  array{recipient: string, description: string, id: string}  $item
     */
    private function page(CardFpdf $pdf, array $item): void
    {
        $pdf->AddPage();

        $this->frame($pdf);

        $pdf->Image(resource_path('images/logo-card.jpg'), (self::WIDTH - 26) / 2, 18, 26, 26);

        $pdf->textHex(self::PRIMARY);
        $pdf->SetFont('Helvetica', 'B', 34);
        $this->center($pdf, 48, 14, mb_strtoupper(__('Certificate')), 34);

        $pdf->textHex(self::MUTED);
        $this->center($pdf, 63, 6, __('is proudly presented to'), 13, 'I');

        $width = 235;
        $size = $pdf->fitFontSize($pdf->latin($item['recipient']), $width, 1, 34, 16);
        $pdf->textHex(self::INK);
        $this->center($pdf, 74, 15, $item['recipient'], $size, 'B');

        $pdf->drawHex(self::ACCENT);
        $pdf->SetLineWidth(0.8);
        $pdf->Line(70, 92, self::WIDTH - 70, 92);

        $pdf->textHex(self::INK);
        $pdf->SetFont('Helvetica', '', 13);
        $y = 98;
        foreach ($pdf->wrap($pdf->latin($item['description']), 200) as $line) {
            $pdf->SetXY(0, $y);
            $pdf->Cell(self::WIDTH, 7, $line, 0, 0, 'C');
            $y += 7;
        }

        $this->signature($pdf);

        $pdf->textHex(self::MUTED);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetXY(20, self::HEIGHT - 17);
        $pdf->Cell(90, 4, $pdf->latin('ID: '.$item['id']));
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
