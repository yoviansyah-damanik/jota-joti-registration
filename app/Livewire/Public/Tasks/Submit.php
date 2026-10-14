<?php

namespace App\Livewire\Public\Tasks;

use App\Enums\TaskStatus;
use App\Models\Participant;
use App\Models\TaskSubmission;
use App\Services\ParticipantIdService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
#[Title('Form Pengiriman Tugas JOTA-JOTI')]
class Submit extends Component
{
    use WithFileUploads;

    public string $participant_code = '';

    public string $jid = '';

    public string $jjid = '';

    public $jota_file = null;

    public $joti_file = null;

    public ?string $notes = null;

    public ?Participant $participant = null;

    public ?TaskSubmission $existingSubmission = null;

    public function mount(?string $code = null): void
    {
        $codeParam = $code ?? request()->query('code');
        if ($codeParam) {
            $this->participant_code = trim((string) $codeParam);
            $this->lookupParticipant();
        }
    }

    public function updatedParticipantCode(): void
    {
        $this->lookupParticipant();
    }

    public function lookupParticipant(): void
    {
        $this->resetErrorBag();
        $this->participant = null;
        $this->existingSubmission = null;

        $raw = trim($this->participant_code);
        if ($raw === '') {
            return;
        }

        $normalized = ParticipantIdService::normalize($raw);

        $found = Participant::query()
            ->with(['troop', 'taskSubmission'])
            ->where(function ($q) use ($raw, $normalized) {
                $q->where('attendance_code', $normalized)
                    ->orWhere('attendance_code', $raw)
                    ->orWhere('kta_number', $raw);
            })
            ->first();

        if (! $found) {
            $this->addError('participant_code', __('ID Peserta tidak ditemukan. Pastikan memasukkan ID Peserta yang tertera di kartu peserta (contoh: P-ABC12345).'));

            return;
        }

        if (! $found->troop?->isVerified()) {
            $this->addError('participant_code', __('Pendaftaran gugusdepan untuk peserta ini belum diverifikasi. Pengiriman tugas hanya dapat dilakukan oleh peserta yang telah diverifikasi.'));

            return;
        }

        $this->participant = $found;
        $this->participant_code = $found->attendance_code ?? $raw;

        if ($found->taskSubmission) {
            $this->existingSubmission = $found->taskSubmission;
            $this->jid = $found->taskSubmission->jid;
            $this->jjid = $found->taskSubmission->jjid;
            $this->notes = $found->taskSubmission->notes;
        }
    }

    public function submit(): void
    {
        $this->lookupParticipant();

        if (! $this->participant) {
            $this->addError('participant_code', __('Silakan masukkan ID Peserta yang valid terlebih dahulu.'));

            return;
        }

        $rules = [
            'jid' => ['required', 'string', 'max:100'],
            'jjid' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Jika belum ada berkas sebelumnya, file PDF wajib diunggah
        if (! $this->existingSubmission || ! $this->existingSubmission->jota_file_path) {
            $rules['jota_file'] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        } else {
            $rules['jota_file'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        }

        if (! $this->existingSubmission || ! $this->existingSubmission->joti_file_path) {
            $rules['joti_file'] = ['required', 'file', 'mimes:pdf', 'max:10240'];
        } else {
            $rules['joti_file'] = ['nullable', 'file', 'mimes:pdf', 'max:10240'];
        }

        $this->validate($rules, [
            'jid.required' => 'JID (JOTA ID) wajib diisi.',
            'jjid.required' => 'JJID (JOTA-JOTI ID) wajib diisi.',
            'jota_file.required' => 'Berkas Tugas JOTA Digital (PDF) wajib diunggah.',
            'jota_file.mimes' => 'Berkas Tugas JOTA Digital harus berupa file format PDF.',
            'jota_file.max' => 'Ukuran berkas Tugas JOTA Digital maksimal 10 MB.',
            'joti_file.required' => 'Berkas Tugas JOTI (PDF) wajib diunggah.',
            'joti_file.mimes' => 'Berkas Tugas JOTI harus berupa file format PDF.',
            'joti_file.max' => 'Ukuran berkas Tugas JOTI maksimal 10 MB.',
        ]);

        $jotaPath = $this->existingSubmission?->jota_file_path;
        if ($this->jota_file) {
            if ($jotaPath && Storage::disk('local')->exists($jotaPath)) {
                Storage::disk('local')->delete($jotaPath);
            }
            $jotaPath = $this->jota_file->store('tasks/jota', 'local');
        }

        $jotiPath = $this->existingSubmission?->joti_file_path;
        if ($this->joti_file) {
            if ($jotiPath && Storage::disk('local')->exists($jotiPath)) {
                Storage::disk('local')->delete($jotiPath);
            }
            $jotiPath = $this->joti_file->store('tasks/joti', 'local');
        }

        // Simpan data submission
        $data = [
            'jid' => trim($this->jid),
            'jjid' => trim($this->jjid),
            'jota_file_path' => $jotaPath,
            'joti_file_path' => $jotiPath,
            'notes' => $this->notes ? trim($this->notes) : null,
            // Jika peserta memperbarui berkas saat revisi atau ditolak, reset status ke pending untuk dinilai ulang
            'jota_status' => TaskStatus::Pending,
            'joti_status' => TaskStatus::Pending,
            'final_status' => TaskStatus::Pending,
        ];

        TaskSubmission::updateOrCreate(
            ['participant_id' => $this->participant->id],
            $data
        );

        session()->flash('toast', [
            'type' => 'success',
            'message' => __('Tugas JOTA Digital & JOTI berhasil dikirim! Silakan pantau hasil penilaian di halaman ini.'),
        ]);

        $this->redirect(route('tasks.status', ['code' => $this->participant->attendance_code]));
    }

    public function render()
    {
        return view('livewire.public.tasks.submit');
    }
}
