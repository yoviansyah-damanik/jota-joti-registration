<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Enums\TroopStatus;
use App\Models\Participant;
use App\Models\Troop;
use Illuminate\Database\Eloquent\Collection;

/**
 * Aturan penerbitan e-sertifikat: gudep terverifikasi mendapat sertifikat gudep,
 * sedangkan peserta mendapat sertifikat bila tugas yang diunggah dinyatakan lulus (selesai).
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
            ->with('taskSubmission')
            ->whereHas('taskSubmission', fn ($q) => $q->where('final_status', TaskStatus::Completed))
            ->reorder()
            ->orderBy('full_name')
            ->get();
    }
}
