<?php

namespace App\Livewire\Admin\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

#[Lazy]
class Recap extends Component
{
    #[Locked]
    public int $sessionId;

    public string $mode = 'troop';

    #[On('attendance-updated')]
    public function refreshRecap(): void
    {
        //
    }

    public function render(AttendanceService $attendance)
    {
        return view('livewire.admin.attendance.recap', [
            'recap' => $attendance->recap(AttendanceSession::findOrFail($this->sessionId)),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }
}
