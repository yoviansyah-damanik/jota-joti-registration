<?php

namespace App\Livewire\Public\Registration;

use App\Enums\DocumentType;
use App\Enums\TroopStatus;
use App\Livewire\Concerns\ParsesParticipantSheet;
use App\Models\Participant;
use App\Services\SettingService;
use App\Support\RegistrationDraft;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Lazy]
class Step2ParticipantUpload extends Component
{
    use ParsesParticipantSheet, WithFileUploads;

    public ?TemporaryUploadedFile $participantFile = null;

    public ?string $savedFileName = null;

    public bool $showManualModal = false;

    public string $manualFullName = '';

    public string $manualBirthDate = '';

    public string $manualGender = 'L';

    public string $manualKta = '';

    public function mount(RegistrationDraft $draft): void
    {
        $this->rows = $draft->participants();
        $this->savedFileName = $draft->file(DocumentType::ParticipantsExcel)['original_name'] ?? null;
    }

    public function openManualModal(): void
    {
        $this->resetErrorBag();
        $this->reset('manualFullName', 'manualBirthDate', 'manualKta');
        $this->manualGender = 'L';
        $this->showManualModal = true;
    }

    public function closeManualModal(): void
    {
        $this->showManualModal = false;
        $this->resetErrorBag();
    }

    public function addManualParticipant(): void
    {
        $this->validate([
            'manualFullName' => ['required', 'string', 'max:255'],
            'manualBirthDate' => ['required', 'date', 'before:today'],
            'manualGender' => ['required', 'in:L,P'],
            'manualKta' => ['nullable', 'string', 'max:50'],
        ], attributes: [
            'manualFullName' => __('Full name'),
            'manualBirthDate' => __('Date of birth'),
            'manualGender' => __('Gender'),
            'manualKta' => __('KTA number'),
        ]);

        $kta = filled($this->manualKta) ? trim($this->manualKta) : '';

        if ($kta !== '') {
            $isDuplicateInRows = collect($this->rows)->contains(fn ($r) => ($r['kta_number'] ?? '') === $kta);
            if ($isDuplicateInRows) {
                $this->addError('manualKta', __('KTA number is duplicated in this file.'));

                return;
            }

            $isDuplicateInDb = Participant::where('kta_number', $kta)
                ->whereHas('troop', fn ($q) => $q->whereIn('status', TroopStatus::activeValues()))
                ->exists();

            if ($isDuplicateInDb) {
                $this->addError('manualKta', __('This KTA number is already registered.'));

                return;
            }
        }

        $this->rows[] = [
            'row' => count($this->rows) + 2,
            'kta_number' => $kta,
            'full_name' => trim($this->manualFullName),
            'birth_date' => $this->manualBirthDate,
            'gender' => $this->manualGender,
            'errors' => [],
        ];

        $this->checkMinimumParticipants();
        $this->closeManualModal();
    }

    public function removeRow(int $index): void
    {
        if (isset($this->rows[$index])) {
            unset($this->rows[$index]);
            $this->rows = array_values($this->rows);
            $this->checkMinimumParticipants();
        }
    }

    private function checkMinimumParticipants(): void
    {
        $minimum = app(SettingService::class)->minParticipantsPerTroop();
        $this->sheetErrors = array_values(array_filter($this->sheetErrors, fn ($e) => ! str_contains($e, 'minimal') && ! str_contains($e, 'at least')));
        if ($this->rows !== [] && count($this->rows) < $minimum) {
            $this->sheetErrors[] = __('A troop must register at least :count participants.', ['count' => $minimum]);
        }
    }

    public function save(RegistrationDraft $draft): void
    {
        if ($this->participantFile) {
            $this->validate($this->participantFileRules());
        } elseif ($this->rows === []) {
            $this->validate($this->participantFileRules());
        }

        if (! $this->sheetIsValid()) {
            $this->addError('participantFile', __('Please fix the errors in the file and upload it again.'));

            return;
        }

        if ($this->participantFile) {
            $draft->storeFile(DocumentType::ParticipantsExcel, $this->participantFile);
        }
        $draft->put('participants', $this->rows);

        $this->dispatch('step-completed');
    }

    public function render()
    {
        return view('livewire.public.registration.step2-participant-upload');
    }
}
