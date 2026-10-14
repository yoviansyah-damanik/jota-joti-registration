<?php

namespace App\Livewire\Admin\Attendance;

use App\Enums\AttendanceStatus;
use App\Livewire\Concerns\UsesDaisyPagination;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Services\AttendanceService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Reactive;
use Livewire\Component;

#[Lazy]
class ParticipantList extends Component
{
    use UsesDaisyPagination;

    #[Locked]
    public int $sessionId;

    #[Reactive]
    public string $search = '';

    #[Reactive]
    public ?string $troopId = null;

    #[Reactive]
    public ?int $groupNumber = null;

    #[Reactive]
    public string $status = '';

    #[Locked]
    public string $filterHash = '';

    public function setStatus(string $participantId, string $status, AttendanceService $attendance): void
    {
        $session = $this->activeSession();
        $participant = Participant::fromVerifiedTroops()->findOrFail($participantId);

        $attendance->record($session, $participant, AttendanceStatus::from($status), auth()->user());

        $this->dispatch('attendance-updated');
    }

    public function markTroopPresent(string $troopId, AttendanceService $attendance): void
    {
        $count = $attendance->markTroopPresent($this->activeSession(), $troopId, auth()->user());

        $this->dispatch('toast', message: __(':count participants marked present.', ['count' => $count]));
        $this->dispatch('attendance-updated');
    }

    private function activeSession(): AttendanceSession
    {
        $session = AttendanceSession::findOrFail($this->sessionId);
        abort_unless($session->is_active, 422, __('This session is closed.'));

        return $session;
    }

    #[On('attendance-updated')]
    public function refreshList(): void
    {
        //
    }

    public function render(AttendanceService $attendance)
    {
        $session = AttendanceSession::findOrFail($this->sessionId);

        $filters = [
            'search' => $this->search,
            'troop_id' => $this->troopId,
            'group_number' => $this->groupNumber,
            'status' => $this->status,
        ];

        // kembali ke halaman 1 bila filter dari parent berubah
        $hash = md5(serialize($filters));
        if ($hash !== $this->filterHash) {
            $this->filterHash = $hash;
            $this->resetPage();
        }

        $participants = $attendance->participantsQuery($session, $filters)
            ->orderBy('troop_id')
            ->orderBy('full_name')
            ->paginate(25)->onEachSide(1);

        return view('livewire.admin.attendance.participant-list', [
            'session' => $session,
            'participants' => $participants,
            'statuses' => AttendanceStatus::cases(),
            'presentByGender' => $attendance->presentByGender($session, $filters),
        ]);
    }
}
