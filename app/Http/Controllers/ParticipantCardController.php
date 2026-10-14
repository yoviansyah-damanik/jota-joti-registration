<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\Troop;
use App\Services\ParticipantCardPdf;
use App\Services\ParticipantIdService;
use App\Services\SettingService;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ParticipantCardController extends Controller
{
    /**
     * Kartu peserta untuk admin (gudep terverifikasi).
     */
    public function admin(Troop $troop, SettingService $settings)
    {
        return $this->render($troop, $settings, 'admin.cards.pdf', ['troop' => $troop]);
    }

    /**
     * Kartu peserta untuk pendaftar; akses lewat kode pendaftaran dan hanya setelah diverifikasi.
     */
    public function registrant(string $code, SettingService $settings)
    {
        $troop = $this->troopByCode($code);

        return $this->render($troop, $settings, 'cards.registrant.pdf', ['code' => $troop->registration_code]);
    }

    /**
     * Kartu peserta dari halaman depan; akses dibatasi middleware cards.published (kartu sudah diterbitkan).
     */
    public function public(Troop $troop, SettingService $settings)
    {
        return $this->render($troop, $settings, 'cards.public.pdf', ['troop' => $troop]);
    }

    /**
     * Unduh PDF kartu (semua peserta, atau satu peserta bila ID-nya diberikan).
     */
    public function adminPdf(Troop $troop, SettingService $settings, ?string $participant = null): Response
    {
        return $this->pdf($troop, $settings, $participant);
    }

    public function registrantPdf(string $code, SettingService $settings, ?string $participant = null): Response
    {
        return $this->pdf($this->troopByCode($code), $settings, $participant);
    }

    public function publicPdf(Troop $troop, SettingService $settings, ?string $participant = null): Response
    {
        return $this->pdf($troop, $settings, $participant);
    }

    private function troopByCode(string $code): Troop
    {
        return Troop::where('registration_code', strtoupper(trim($code)))->firstOrFail();
    }

    /**
     * @return Collection<int, Participant>
     */
    private function participants(Troop $troop): Collection
    {
        abort_unless($troop->status->value === 'verified', 404);

        return $troop->participants()->whereNotNull('attendance_code')->get();
    }

    /**
     * @param  array<string, mixed>  $routeArgs  parameter rute unduhan PDF sesuai jalur akses
     */
    private function render(Troop $troop, SettingService $settings, string $pdfRoute, array $routeArgs)
    {
        return view('print.participant-cards', [
            'troop' => $troop,
            'participants' => $this->participants($troop),
            'eventName' => $settings->get('event_name'),
            'organizationName' => $settings->get('organization_name'),
            'pdfUrl' => fn (?Participant $participant = null) => route($pdfRoute, $participant
                ? [...$routeArgs, 'participant' => $participant->attendance_code]
                : $routeArgs),
        ]);
    }

    private function pdf(Troop $troop, SettingService $settings, ?string $participantCode): Response
    {
        $participants = $this->participants($troop);
        $name = 'kartu-'.$troop->troop_number;

        if ($participantCode !== null) {
            $participants = $participants->where('attendance_code', ParticipantIdService::normalize($participantCode))->values();
            $name .= '-'.Str::slug((string) $participants->first()?->full_name);
        }

        abort_if($participants->isEmpty(), 404);

        $content = app(ParticipantCardPdf::class)->render($troop, $participants, (string) $settings->get('event_name'), (string) $settings->get('organization_name'));

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$name.'.pdf"',
            'Content-Length' => (string) strlen($content),
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
