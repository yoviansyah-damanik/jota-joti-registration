<?php

namespace App\Livewire\Public;

use App\Enums\DocumentType;
use App\Livewire\Concerns\ParsesParticipantSheet;
use App\Livewire\Concerns\ThrottlesActions;
use App\Livewire\Public\Registration\Step3Documents;
use App\Models\Troop;
use App\Services\RegistrationService;
use App\Support\RegistrationDraft;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Lazy]
class RevisionForm extends Component
{
    use ParsesParticipantSheet, ThrottlesActions, WithFileUploads;

    #[Locked]
    public string $troopId;

    #[Locked]
    public string $code;

    public ?TemporaryUploadedFile $participantFile = null;

    public ?TemporaryUploadedFile $assignmentLetter = null;

    public ?TemporaryUploadedFile $paymentProof = null;

    public function mount(string $troopId, string $code): void
    {
        $this->troopId = $troopId;
        $this->code = $code;

        abort_unless($this->troop()->isEditableByRegistrant(), 403);
    }

    /**
     * Revisi hanya untuk gudep dengan kode yang cocok dan status "perlu revisi".
     */
    private function troop(): Troop
    {
        return Troop::whereKey($this->troopId)->where('registration_code', $this->code)->firstOrFail();
    }

    protected function ignoredTroopId(): ?string
    {
        return $this->troopId;
    }

    protected function participantFileRules(): array
    {
        return ['participantFile' => ['nullable', 'file', 'mimes:xlsx,xls', 'max:5120']];
    }

    public function submit(RegistrationService $registrations): void
    {
        $this->throttle('registration-revision', 'revision', maxAttempts: 5);

        $this->validate([
            ...$this->participantFileRules(),
            'assignmentLetter' => ['nullable', ...Step3Documents::FILE_RULES],
            'paymentProof' => ['nullable', ...Step3Documents::FILE_RULES],
        ]);

        if (! $this->participantFile && ! $this->assignmentLetter && ! $this->paymentProof) {
            $this->addError('revision', __('Upload at least one corrected file.'));

            return;
        }

        if ($this->participantFile && ! $this->sheetIsValid()) {
            $this->addError('participantFile', __('Please fix the errors in the file and upload it again.'));

            return;
        }

        $troop = $this->troop();
        abort_unless($troop->isEditableByRegistrant(), 403);

        $files = collect([
            DocumentType::ParticipantsExcel->value => $this->participantFile,
            DocumentType::AssignmentLetter->value => $this->assignmentLetter,
            DocumentType::PaymentProof->value => $this->paymentProof,
        ])->filter()->map(fn (TemporaryUploadedFile $file) => [
            'path' => $file->store('drafts/revision-'.$troop->id, RegistrationDraft::DISK),
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ])->all();

        $registrations->revise($troop, $this->participantFile ? $this->rows : null, $files);

        $this->reset('participantFile', 'assignmentLetter', 'paymentProof', 'rows', 'sheetErrors');
        $this->dispatch('revision-submitted');
        $this->dispatch('toast', message: __('Revision submitted. The committee will review it again.'));
    }

    public function render()
    {
        return view('livewire.public.revision-form');
    }
}
