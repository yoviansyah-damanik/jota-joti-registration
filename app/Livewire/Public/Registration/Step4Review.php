<?php

namespace App\Livewire\Public\Registration;

use App\Enums\DocumentType;
use App\Livewire\Concerns\ThrottlesActions;
use App\Services\RegistrationService;
use App\Support\RegistrationDraft;
use Livewire\Attributes\Lazy;
use Livewire\Component;

#[Lazy]
class Step4Review extends Component
{
    use ThrottlesActions;

    public bool $confirmed = false;

    public function submit(RegistrationDraft $draft, RegistrationService $registrations)
    {
        $this->validate(['confirmed' => 'accepted'], ['confirmed.accepted' => __('Please confirm that the data is correct.')]);
        $this->throttle('registration-submit', 'confirmed', maxAttempts: 5);

        $troop = $registrations->submit($draft);

        session()->forget('registration.agreed');
        session()->flash('registration.completed', $troop->id);

        return $this->redirectRoute('register.completed', navigate: true);
    }

    /**
     * Data modal pratinjau untuk satu dokumen draft; null bila belum diunggah.
     * kind: image/pdf dibuka di modal, selain itu (Excel) hanya bisa diunduh.
     */
    private function previewPayload(RegistrationDraft $draft, DocumentType $type): ?array
    {
        $file = $draft->file($type);

        if (! $file) {
            return null;
        }

        $mime = $file['mime'] ?? '';
        $kind = match (true) {
            $mime === 'application/pdf' => 'pdf',
            str_starts_with($mime, 'image/') => 'image',
            default => null,
        };

        return [
            'url' => route('register.document', [$type->value, 'inline' => 1], absolute: false),
            'download' => route('register.document', $type->value, absolute: false),
            'title' => $type->label(),
            'name' => $file['original_name'],
            'kind' => $kind,
        ];
    }

    public function render(RegistrationDraft $draft)
    {
        $participants = collect($draft->participants());

        return view('livewire.public.registration.step4-review', [
            'troop' => $draft->troop(),
            'participants' => $participants,
            'genderCounts' => $participants->countBy('gender'),
            'documents' => collect(DocumentType::cases())->mapWithKeys(fn (DocumentType $type) => [
                $type->label() => $draft->file($type)['original_name'] ?? '-',
            ]),
            'previews' => collect(DocumentType::cases())->mapWithKeys(fn (DocumentType $type) => [$type->label() => $this->previewPayload($draft, $type)]),
        ]);
    }
}
