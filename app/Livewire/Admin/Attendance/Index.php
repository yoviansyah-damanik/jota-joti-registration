<?php

namespace App\Livewire\Admin\Attendance;

use App\Enums\AttendanceStatus;
use App\Models\AttendanceSession;
use App\Models\Troop;
use App\Services\AttendanceService;
use App\Services\SettingService;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    #[Url(as: 'session')]
    public ?int $sessionId = null;

    #[Url(except: '')]
    public string $search = '';

    #[Url(as: 'troop')]
    public ?string $troopId = null;

    #[Url(as: 'group')]
    public ?int $groupNumber = null;

    #[Url(except: '')]
    public string $status = '';

    public string $code = '';

    /** Hasil scan terakhir: status success|duplicate|error beserta data peserta. */
    public ?array $lastCheckIn = null;

    public function mount(): void
    {
        $this->sessionId ??= AttendanceSession::where('is_active', true)->orderBy('date')->value('id')
            ?? AttendanceSession::latest('date')->value('id');
    }

    /**
     * Dipanggil scanner kamera: hasil decode QR (ID peserta) langsung dicatat.
     */
    public function scan(string $code, AttendanceService $attendance): void
    {
        $this->code = mb_substr($code, 0, 50);
        $this->checkIn($attendance);
    }

    /**
     * Check-in dari input ketik, scanner USB/Bluetooth (bekerja seperti keyboard + Enter), atau kamera.
     */
    public function checkIn(AttendanceService $attendance): void
    {
        $this->resetErrorBag();
        $this->validate(['code' => ['required', 'string', 'max:50']], attributes: ['code' => __('participant ID')]);

        $session = AttendanceSession::findOrFail($this->sessionId);
        if (! $session->is_active) {
            $this->fail(__('This session is closed.'));

            return;
        }

        try {
            $record = $attendance->checkInByCode($session, $this->code, auth()->user());
        } catch (ValidationException $e) {
            $this->fail(collect($e->errors())->flatten()->first());

            return;
        }

        $participant = $record->participant->load('troop');
        $duplicate = ! $record->wasRecentlyCreated && ! $record->wasChanged();

        $this->lastCheckIn = [
            'status' => $duplicate ? 'duplicate' : 'success',
            'name' => $participant->full_name,
            'code' => $participant->attendance_code,
            'kta' => $participant->kta_number,
            'troop' => $participant->troop->number_label.' – '.$participant->troop->troop_name,
            'group' => $participant->group_number,
            'time' => $record->checked_in_at?->format('H:i:s'),
        ];

        $this->reset('code');
        $this->dispatch('scan-result', status: $this->lastCheckIn['status']);
        $this->dispatch('attendance-updated');
    }

    private function fail(string $message): void
    {
        $this->lastCheckIn = ['status' => 'error', 'message' => $message];
        $this->reset('code');
        $this->dispatch('scan-result', status: 'error');
    }

    public function render(SettingService $settings)
    {
        $sessions = AttendanceSession::orderBy('date')->orderBy('starts_at')->get();

        return view('livewire.admin.attendance.index', [
            'sessions' => $sessions,
            'session' => $sessions->firstWhere('id', $this->sessionId),
            'troopOptions' => Troop::verified()->orderBy('troop_number')->get()
                ->mapWithKeys(fn (Troop $troop) => [$troop->id => $troop->number_label.' – '.$troop->troop_name])->all(),
            'groupOptions' => collect(range(1, $settings->maxGroupNumber()))->mapWithKeys(fn ($n) => [$n => __('Group :n', ['n' => $n])])->all(),
            'statusOptions' => collect(AttendanceStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])
                ->put('unrecorded', __('Not recorded'))->all(),
        ])->title(__('Attendance'));
    }
}
