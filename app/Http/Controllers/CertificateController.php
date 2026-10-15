<?php

namespace App\Http\Controllers;

use App\Models\Troop;
use App\Services\CertificatePdf;
use App\Services\CertificateService;
use App\Services\ParticipantIdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificates, private CertificatePdf $pdf) {}

    /**
     * Sertifikat gudep untuk pendaftar (akses lewat kode pendaftaran).
     */
    public function troop(string $code): Response
    {
        return $this->troopPdf($this->troopByCode($code));
    }

    /**
     * Sertifikat peserta untuk pendaftar: semua peserta yang berhak, atau satu peserta bila ID-nya diberikan.
     */
    public function participants(string $code, ?string $participant = null): Response|RedirectResponse
    {
        return $this->participantsPdf($this->troopByCode($code), $participant);
    }

    public function adminTroop(Troop $troop): Response
    {
        return $this->troopPdf($troop);
    }

    public function adminParticipants(Troop $troop, ?string $participant = null): Response|RedirectResponse
    {
        return $this->participantsPdf($troop, $participant);
    }

    /**
     * Preview contoh sertifikat (peserta atau gudep) untuk admin.
     */
    public function previewTemplate(string $type): Response
    {
        $content = $this->pdf->previewSample($type);
        $filename = 'preview-sertifikat-'.($type === 'troop' ? 'gudep' : 'peserta');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function troopByCode(string $code): Troop
    {
        return Troop::where('registration_code', strtoupper(trim($code)))->firstOrFail();
    }

    private function troopPdf(Troop $troop): Response
    {
        abort_unless($this->certificates->troopEligible($troop), 404);

        return $this->download($this->pdf->troop($troop), 'sertifikat-gudep-'.$troop->troop_number);
    }

    private function participantsPdf(Troop $troop, ?string $participantCode): Response|RedirectResponse
    {
        $participants = $this->certificates->eligibleParticipants($troop);
        $name = 'sertifikat-peserta-'.$troop->troop_number;

        // permintaan "semua peserta" tanpa peserta yang berhak: kembali dengan pesan, bukan halaman 404
        if ($participantCode === null && $participants->isEmpty() && $this->certificates->troopEligible($troop)) {
            return back(fallback: route('home'))->with('toast', [
                'message' => __('Belum ada peserta yang dinyatakan lulus tugas, sehingga sertifikat peserta belum tersedia.'),
                'type' => 'warning',
            ]);
        }

        if ($participantCode !== null) {
            $participants = $participants->where('attendance_code', ParticipantIdService::normalize($participantCode))->values();
            $name .= '-'.Str::slug((string) $participants->first()?->full_name);
        }

        abort_if($participants->isEmpty(), 404);

        return $this->download($this->pdf->participants($troop, $participants), $name);
    }

    private function download(string $content, string $name): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$name.'.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
