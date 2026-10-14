<?php

namespace App\Livewire\Admin;

use App\Services\SettingService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Settings extends Component
{
    public string $event_name = '';

    public string $organization_name = '';

    public bool $registration_open = true;

    public int $max_group_number = 5;

    public int $min_per_number = 1;

    public int $registration_fee = 0;

    public string $bank_account_info = '';

    public string $contact_info = '';

    public string $additional_terms = '';

    public bool $event_completed = false;

    public string $certificate_signer_name = '';

    public string $certificate_signer_title = '';

    public string $certificate_city = '';

    public function mount(SettingService $settings): void
    {
        $values = $settings->all();

        $this->fill([
            'event_name' => (string) $values['event_name'],
            'organization_name' => (string) $values['organization_name'],
            'registration_open' => $settings->registrationOpen(),
            'max_group_number' => $settings->maxGroupNumber(),
            'min_per_number' => $settings->minPerNumber(),
            'registration_fee' => (int) $values['registration_fee'],
            'bank_account_info' => (string) $values['bank_account_info'],
            'contact_info' => (string) $values['contact_info'],
            'additional_terms' => (string) $values['additional_terms'],
            'event_completed' => $settings->eventCompleted(),
            'certificate_signer_name' => (string) $values['certificate_signer_name'],
            'certificate_signer_title' => (string) $values['certificate_signer_title'],
            'certificate_city' => (string) $values['certificate_city'],
        ]);
    }

    public function save(SettingService $settings): void
    {
        $data = $this->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'organization_name' => ['required', 'string', 'max:100'],
            'registration_open' => ['boolean'],
            'max_group_number' => ['required', 'integer', 'min:1', 'max:50'],
            'min_per_number' => ['required', 'integer', 'min:0', 'max:20'],
            'registration_fee' => ['required', 'integer', 'min:0'],
            'bank_account_info' => ['nullable', 'string', 'max:500'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'additional_terms' => ['nullable', 'string', 'max:5000'],
            'event_completed' => ['boolean'],
            'certificate_signer_name' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:100'],
            'certificate_signer_title' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:150'],
            'certificate_city' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:60'],
        ]);

        // tanggal sertifikat = tanggal kegiatan dinyatakan selesai (tidak berubah bila disimpan ulang)
        $data['event_completed_at'] = match (true) {
            ! $this->event_completed => '',
            $settings->eventCompleted() => (string) $settings->get('event_completed_at'),
            default => now()->toDateString(),
        };

        $settings->save($data);

        $this->dispatch('toast', message: __('Settings saved.'));
    }

    public function render()
    {
        return view('livewire.admin.settings')->title(__('Settings'));
    }
}
