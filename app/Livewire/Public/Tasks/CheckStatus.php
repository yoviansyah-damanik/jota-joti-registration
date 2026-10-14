<?php

namespace App\Livewire\Public\Tasks;

use App\Models\Participant;
use App\Models\TaskSubmission;
use App\Services\ParticipantIdService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Cek Penilaian Tugas JOTA-JOTI')]
class CheckStatus extends Component
{
    public string $code = '';

    public ?Participant $participant = null;

    public ?TaskSubmission $submission = null;

    public bool $searched = false;

    public function mount(?string $code = null): void
    {
        $codeParam = $code ?? ($this->code !== '' ? $this->code : request()->query('code'));
        if ($codeParam) {
            $this->code = trim((string) $codeParam);
            $this->check();
        }
    }

    public function check(): void
    {
        $this->resetErrorBag();
        $this->participant = null;
        $this->submission = null;
        $this->searched = true;

        $raw = trim($this->code);
        if ($raw === '') {
            $this->addError('code', __('Silakan masukkan ID Peserta terlebih dahulu.'));

            return;
        }

        $normalized = ParticipantIdService::normalize($raw);

        $found = Participant::query()
            ->with(['troop', 'taskSubmission.assessor'])
            ->where(function ($q) use ($raw, $normalized) {
                $q->where('attendance_code', $normalized)
                    ->orWhere('attendance_code', $raw)
                    ->orWhere('kta_number', $raw);
            })
            ->first();

        if (! $found) {
            $this->addError('code', __('ID Peserta tidak ditemukan dalam sistem.'));

            return;
        }

        $this->participant = $found;
        $this->code = $found->attendance_code ?? $raw;
        $this->submission = $found->taskSubmission;
    }

    public function render()
    {
        return view('livewire.public.tasks.check-status');
    }
}
