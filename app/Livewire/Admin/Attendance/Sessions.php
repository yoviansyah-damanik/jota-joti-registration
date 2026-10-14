<?php

namespace App\Livewire\Admin\Attendance;

use App\Models\AttendanceSession;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.admin')]
class Sessions extends Component
{
    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $date = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public bool $is_active = true;

    public function edit(int $id): void
    {
        $session = AttendanceSession::findOrFail($id);

        $this->editingId = $session->id;
        $this->name = $session->name;
        $this->date = $session->date->format('Y-m-d');
        $this->starts_at = substr((string) $session->starts_at, 0, 5);
        $this->ends_at = substr((string) $session->ends_at, 0, 5);
        $this->is_active = $session->is_active;
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'starts_at' => ['nullable', 'date_format:H:i'],
            'ends_at' => ['nullable', 'date_format:H:i', 'after:starts_at'],
            'is_active' => ['boolean'],
        ], attributes: [
            'name' => __('session name'),
            'date' => __('date'),
            'starts_at' => __('start time'),
            'ends_at' => __('end time'),
        ]);

        $data['starts_at'] = $data['starts_at'] ?: null;
        $data['ends_at'] = $data['ends_at'] ?: null;

        AttendanceSession::updateOrCreate(['id' => $this->editingId], $data);

        $this->resetForm();
        $this->dispatch('toast', message: __('Session saved.'));
    }

    public function toggle(int $id): void
    {
        $session = AttendanceSession::findOrFail($id);
        $session->update(['is_active' => ! $session->is_active]);
    }

    public function delete(int $id): void
    {
        AttendanceSession::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->resetForm();
        }

        $this->dispatch('toast', message: __('Session deleted.'));
    }

    private function resetForm(): void
    {
        $this->reset('editingId', 'name', 'date', 'starts_at', 'ends_at', 'is_active');
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.admin.attendance.sessions', [
            'sessions' => AttendanceSession::withCount(['attendances as present_count' => fn ($q) => $q->where('status', 'present')])
                ->orderBy('date')->orderBy('starts_at')->get(),
        ])->title(__('Attendance Sessions'));
    }
}
