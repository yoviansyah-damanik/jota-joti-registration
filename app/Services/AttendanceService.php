<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\Gender;
use App\Enums\TroopStatus;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    /**
     * Query peserta yang berhak diabsen (hanya dari gudep terverifikasi) beserta absensinya di sesi tertentu.
     */
    public function participantsQuery(AttendanceSession $session, array $filters = []): Builder
    {
        return Participant::query()
            ->fromVerifiedTroops()
            ->with(['troop:id,troop_number,troop_number_pair,troop_name,unit', 'attendances' => fn ($q) => $q->where('attendance_session_id', $session->id)])
            ->search($filters['search'] ?? null)
            ->when($filters['troop_id'] ?? null, fn ($q, $troopId) => $q->where('troop_id', $troopId))
            ->when($filters['group_number'] ?? null, fn ($q, $number) => $q->where('group_number', $number))
            ->when($filters['status'] ?? null, function ($q, $status) use ($session) {
                $status === 'unrecorded'
                    ? $q->whereDoesntHave('attendances', fn ($q) => $q->where('attendance_session_id', $session->id))
                    : $q->whereHas('attendances', fn ($q) => $q->where('attendance_session_id', $session->id)->where('status', $status));
            });
    }

    /**
     * @return Collection<int, Participant> dengan properti tambahan ->attendance
     */
    public function participantsWithStatus(AttendanceSession $session, array $filters = []): Collection
    {
        return $this->participantsQuery($session, $filters)
            ->join('troops', 'troops.id', '=', 'participants.troop_id')
            ->orderBy('troops.troop_number')
            ->orderBy('participants.full_name')
            ->select('participants.*')
            ->get()
            ->each(fn (Participant $p) => $p->setRelation('attendance', $p->attendances->first()));
    }

    /**
     * Jumlah peserta hadir putra dan putri dari hasil query (mengikuti filter yang dipakai).
     *
     * @return array{male: int, female: int}
     */
    public function presentByGender(AttendanceSession $session, array $filters = []): array
    {
        $counts = $this->participantsQuery($session, $filters)
            ->whereHas('attendances', fn ($q) => $q->where('attendance_session_id', $session->id)->where('status', AttendanceStatus::Present))
            ->toBase()
            ->reorder()
            ->selectRaw('participants.gender, count(*) as total')
            ->groupBy('participants.gender')
            ->pluck('total', 'gender');

        return [
            'male' => (int) ($counts[Gender::Male->value] ?? 0),
            'female' => (int) ($counts[Gender::Female->value] ?? 0),
        ];
    }

    public function record(AttendanceSession $session, Participant $participant, AttendanceStatus $status, ?User $recorder = null, ?string $note = null): Attendance
    {
        $participant->loadMissing('troop');

        if ($participant->troop->status !== TroopStatus::Verified) {
            throw ValidationException::withMessages(['code' => __('This participant\'s troop has not been verified.')]);
        }

        return Attendance::updateOrCreate(
            ['attendance_session_id' => $session->id, 'participant_id' => $participant->id],
            [
                'status' => $status,
                'checked_in_at' => $status === AttendanceStatus::Present ? now() : null,
                'recorded_by' => $recorder?->id,
                'note' => $note,
            ],
        );
    }

    /**
     * Check-in dari hasil scan QR (ID peserta). Nomor KTA tetap diterima sebagai cadangan ketik manual.
     * Scan ulang tidak mengubah jam hadir pertama; cek ->wasRecentlyCreated / ->wasChanged() untuk mendeteksinya.
     */
    public function checkInByCode(AttendanceSession $session, string $code, ?User $recorder = null): Attendance
    {
        $raw = trim($code);
        $participant = Participant::fromVerifiedTroops()
            ->where(fn ($q) => $q->where('attendance_code', ParticipantIdService::normalize($raw))->orWhere('kta_number', $raw))
            ->first();

        if (! $participant) {
            throw ValidationException::withMessages(['code' => __('No verified participant with ID :code.', ['code' => $raw])]);
        }

        $existing = Attendance::where('attendance_session_id', $session->id)
            ->where('participant_id', $participant->id)
            ->where('status', AttendanceStatus::Present)
            ->first();

        return $existing ?? $this->record($session, $participant, AttendanceStatus::Present, $recorder);
    }

    /**
     * Menandai hadir seluruh peserta satu gudep yang belum tercatat.
     */
    public function markTroopPresent(AttendanceSession $session, string $troopId, ?User $recorder = null): int
    {
        $participants = Participant::fromVerifiedTroops()
            ->where('troop_id', $troopId)
            ->whereDoesntHave('attendances', fn ($q) => $q->where('attendance_session_id', $session->id))
            ->get();

        foreach ($participants as $participant) {
            $this->record($session, $participant, AttendanceStatus::Present, $recorder);
        }

        return $participants->count();
    }

    /**
     * Rekap kehadiran per gudep dan per nomor grup.
     *
     * @return array{total: int, counts: array<string, int>, by_troop: Collection, by_group: Collection}
     */
    public function recap(AttendanceSession $session): array
    {
        $participants = $this->participantsWithStatus($session);
        $statusOf = fn (Participant $p) => $p->attendance?->status->value ?? 'unrecorded';

        $presentBy = fn (Collection $group, Gender $gender) => $group
            ->filter(fn ($p) => $p->gender === $gender && $statusOf($p) === AttendanceStatus::Present->value)
            ->count();

        $summarize = fn (Collection $group) => [
            'total' => $group->count(),
            'present_male' => $presentBy($group, Gender::Male),
            'present_female' => $presentBy($group, Gender::Female),
            ...collect([...array_column(AttendanceStatus::cases(), 'value'), 'unrecorded'])
                ->mapWithKeys(fn ($status) => [$status => $group->filter(fn ($p) => $statusOf($p) === $status)->count()])
                ->all(),
        ];

        return [
            'total' => $participants->count(),
            'counts' => $summarize($participants),
            'by_troop' => $participants->groupBy('troop_id')->map(fn (Collection $group) => [
                'troop' => $group->first()->troop,
                ...$summarize($group),
            ])->values(),
            'by_group' => $participants->groupBy(fn ($p) => $p->group_number ?? 0)->sortKeys()->map(fn (Collection $group, $number) => [
                'number' => $number,
                ...$summarize($group),
            ])->values(),
        ];
    }
}
