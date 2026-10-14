<?php

namespace App\Livewire\Public\Registration;

use App\Enums\DocumentType;
use App\Support\RegistrationDraft;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Lazy]
class Step3Documents extends Component
{
    use WithFileUploads;

    public const FILE_RULES = ['file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];

    public ?TemporaryUploadedFile $assignmentLetter = null;

    public ?TemporaryUploadedFile $paymentProof = null;

    /** @var array<string, string|null> */
    public array $savedFiles = [];

    public function mount(RegistrationDraft $draft): void
    {
        $this->savedFiles = [
            'assignmentLetter' => $draft->file(DocumentType::AssignmentLetter)['original_name'] ?? null,
            'paymentProof' => $draft->file(DocumentType::PaymentProof)['original_name'] ?? null,
        ];
    }

    public function save(RegistrationDraft $draft): void
    {
        $this->validate([
            'assignmentLetter' => [$this->savedFiles['assignmentLetter'] ? 'nullable' : 'required', ...self::FILE_RULES],
            'paymentProof' => [$this->savedFiles['paymentProof'] ? 'nullable' : 'required', ...self::FILE_RULES],
        ], attributes: [
            'assignmentLetter' => __('assignment letter'),
            'paymentProof' => __('proof of payment'),
        ]);

        if ($this->assignmentLetter) {
            $draft->storeFile(DocumentType::AssignmentLetter, $this->assignmentLetter);
        }

        if ($this->paymentProof) {
            $draft->storeFile(DocumentType::PaymentProof, $this->paymentProof);
        }

        $this->dispatch('step-completed');
    }

    public function render(RegistrationDraft $draft)
    {
        return view('livewire.public.registration.step3-documents', [
            'settings' => app(\App\Services\SettingService::class),
            'participantCount' => count($draft->participants()),
        ]);
    }
}
