<?php

namespace App\Livewire\Admin;

use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Settings extends Component
{
    use WithFileUploads;

    public $logo = null;

    public ?string $currentLogoUrl = null;

    public bool $hasCustomLogo = false;

    public string $event_name = '';

    public string $organization_name = '';

    public bool $registration_open = false;

    public int $penggalang_group_count = 5;

    public int $penegak_group_count = 5;

    public int $penggalang_start_number = 1;

    public ?int $penegak_start_number = null;

    public int $max_group_number = 10;

    public int $min_per_number = 1;

    public int $registration_fee = 0;

    public string $bank_account_info = '';

    public string $contact_info = '';

    public string $additional_terms = '';

    public bool $event_completed = false;

    public string $certificate_signer_name = '';

    public string $certificate_signer_title = '';

    public string $certificate_city = '';

    public bool $task_submission_enabled = true;

    public ?string $task_submission_open_at = null;

    public ?string $task_submission_close_at = null;

    public function penggalangRange(): string
    {
        $start = max(1, (int) $this->penggalang_start_number);
        $count = max(1, (int) $this->penggalang_group_count);
        $end = $start + $count - 1;

        return $start === $end ? "Grup {$start}" : "Grup {$start} – {$end} ({$count} grup)";
    }

    public function penegakRange(): string
    {
        $pCount = max(1, (int) $this->penggalang_group_count);
        $pStart = max(1, (int) $this->penggalang_start_number);
        $start = (int) $this->penegak_start_number > 0 ? (int) $this->penegak_start_number : ($pStart + $pCount);
        $count = max(1, (int) $this->penegak_group_count);
        $end = $start + $count - 1;

        return $start === $end ? "Grup {$start}" : "Grup {$start} – {$end} ({$count} grup)";
    }

    public function mount(SettingService $settings): void
    {
        $values = $settings->all();

        $this->currentLogoUrl = $settings->logoUrl();
        $this->hasCustomLogo = $settings->hasCustomLogo();

        $this->fill([
            'event_name' => (string) $values['event_name'],
            'organization_name' => (string) $values['organization_name'],
            'registration_open' => $settings->registrationOpen(),
            'penggalang_group_count' => $settings->penggalangGroupCount(),
            'penegak_group_count' => $settings->penegakGroupCount(),
            'penggalang_start_number' => $settings->penggalangStartNumber(),
            'penegak_start_number' => (int) $values['penegak_start_number'] ?: null,
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
            'task_submission_enabled' => $settings->taskSubmissionEnabled(),
            'task_submission_open_at' => ! empty($values['task_submission_open_at']) ? date('Y-m-d\TH:i', strtotime($values['task_submission_open_at'])) : null,
            'task_submission_close_at' => ! empty($values['task_submission_close_at']) ? date('Y-m-d\TH:i', strtotime($values['task_submission_close_at'])) : null,
        ]);
    }

    public function save(SettingService $settings): void
    {
        $data = $this->validate([
            'event_name' => ['required', 'string', 'max:100'],
            'organization_name' => ['required', 'string', 'max:100'],
            'registration_open' => ['boolean'],
            'penggalang_group_count' => ['required', 'integer', 'min:1', 'max:50'],
            'penegak_group_count' => ['required', 'integer', 'min:1', 'max:50'],
            'penggalang_start_number' => ['required', 'integer', 'min:1', 'max:100'],
            'penegak_start_number' => ['nullable', 'integer', 'min:1', 'max:100'],
            'min_per_number' => ['required', 'integer', 'min:0', 'max:20'],
            'registration_fee' => ['required', 'integer', 'min:0'],
            'bank_account_info' => ['nullable', 'string', 'max:500'],
            'contact_info' => ['nullable', 'string', 'max:255'],
            'additional_terms' => ['nullable', 'string', 'max:5000'],
            'event_completed' => ['boolean'],
            'certificate_signer_name' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:100'],
            'certificate_signer_title' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:150'],
            'certificate_city' => [Rule::requiredIf($this->event_completed), 'nullable', 'string', 'max:60'],
            'task_submission_enabled' => ['boolean'],
            'task_submission_open_at' => ['nullable', 'date'],
            'task_submission_close_at' => ['nullable', 'date', 'after_or_equal:task_submission_open_at'],
        ]);

        $data['task_submission_open_at'] = $this->task_submission_open_at ? date('Y-m-d H:i:s', strtotime($this->task_submission_open_at)) : '';
        $data['task_submission_close_at'] = $this->task_submission_close_at ? date('Y-m-d H:i:s', strtotime($this->task_submission_close_at)) : '';

        $penegakStart = $this->penegak_start_number ?: ($this->penggalang_start_number + $this->penggalang_group_count);
        $data['max_group_number'] = max(
            $this->penggalang_start_number + $this->penggalang_group_count - 1,
            $penegakStart + $this->penegak_group_count - 1,
        );

        // tanggal sertifikat = tanggal kegiatan dinyatakan selesai (tidak berubah bila disimpan ulang)
        $data['event_completed_at'] = match (true) {
            ! $this->event_completed => '',
            $settings->eventCompleted() => (string) $settings->get('event_completed_at'),
            default => now()->toDateString(),
        };

        // Unggah logo resmi baru jika disediakan
        if ($this->logo) {
            $this->validate([
                'logo' => ['image', 'max:2048', 'mimes:png,jpg,jpeg,webp,svg'],
            ]);

            // Hapus logo kustom lama jika ada
            $oldPath = $settings->get('official_logo_path');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $storedPath = $this->logo->store('logos', 'public');
            $data['official_logo_path'] = $storedPath;
        }

        $settings->save($data);

        $this->currentLogoUrl = $settings->logoUrl();
        $this->hasCustomLogo = $settings->hasCustomLogo();
        $this->logo = null;

        $this->dispatch('toast', message: __('Settings saved.'));
    }

    /**
     * Hapus logo kustom dan kembalikan ke logo bawaan aplikasi.
     */
    public function deleteLogo(SettingService $settings): void
    {
        $oldPath = $settings->get('official_logo_path');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $settings->save(['official_logo_path' => '']);
        $this->currentLogoUrl = $settings->logoUrl();
        $this->hasCustomLogo = false;
        $this->logo = null;

        $this->dispatch('toast', message: __('Logo reset to default.'));
    }

    public function render()
    {
        return view('livewire.admin.settings')->title(__('Settings'));
    }
}
