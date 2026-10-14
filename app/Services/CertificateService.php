<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\TroopStatus;
use App\Models\Participant;
use App\Models\Troop;
use Illuminate\Database\Eloquent\Collection;

/**
 * Aturan penerbitan e-sertifikat: gudep terverifikasi mendapat sertifikat gudep,
 * sedangkan peserta mendapat sertifikat bila hadir minimal satu sesi absensi.
 */
class CertificateService
{
    public function __construct(private SettingService $settings) {}

    /**
     * Sertifikat baru dapat diunduh publik setelah kegiatan dinyatakan selesai.
     */
    public function published(): bool
    {
        return $this->settings->eventCompleted();
    }

    public function troopEligible(Troop $troop): bool
    {
        return $troop->status === TroopStatus::Verified;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function eligibleParticipants(Troop $troop): Collection
    {
        if (! $this->troopEligible($troop)) {
            return new Collection;
        }

        return $troop->participants()
            ->whereHas('attendances', fn ($q) => $q->where('status', AttendanceStatus::Present))
            ->reorder()
            ->orderBy('full_name')
            ->get();
    }
}
