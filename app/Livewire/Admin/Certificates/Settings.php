<?php

namespace App\Livewire\Admin\Certificates;

use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
#[Title('Pengaturan Sertifikat')]
class Settings extends Component
{
    use WithFileUploads;

    public string $activeTab = 'participant';

    // Sertifikat Peserta
    public $participant_template;
    public ?string $participant_template_url = null;
    public float $p_name_x = 50;
    public float $p_name_y = 45;
    public float $p_name_size = 28;
    public string $p_name_color = '#1f1a33';
    public string $p_name_align = 'center';
    public bool $p_name_bold = true;

    public float $p_id_x = 50;
    public float $p_id_y = 54;
    public float $p_id_size = 14;
    public string $p_id_color = '#5b21b6';
    public string $p_id_align = 'center';
    public bool $p_id_bold = false;

    // Sertifikat Gugusdepan
    public $troop_template;
    public ?string $troop_template_url = null;
    public float $t_name_x = 50;
    public float $t_name_y = 45;
    public float $t_name_size = 28;
    public string $t_name_color = '#1f1a33';
    public string $t_name_align = 'center';
    public bool $t_name_bold = true;

    public float $t_number_x = 50;
    public float $t_number_y = 54;
    public float $t_number_size = 18;
    public string $t_number_color = '#5b21b6';
    public string $t_number_align = 'center';
    public bool $t_number_bold = true;

    public function mount(SettingService $settings): void
    {
        $this->loadSettings($settings);
    }

    public function loadSettings(SettingService $settings): void
    {
        $this->participant_template_url = $settings->certificateParticipantTemplateUrl();
        $pLayout = $settings->certificateParticipantLayout();

        $this->p_name_x = (float) ($pLayout['full_name']['x'] ?? 50);
        $this->p_name_y = (float) ($pLayout['full_name']['y'] ?? 45);
        $this->p_name_size = (float) ($pLayout['full_name']['font_size'] ?? 28);
        $this->p_name_color = (string) ($pLayout['full_name']['color'] ?? '#1f1a33');
        $this->p_name_align = (string) ($pLayout['full_name']['align'] ?? 'center');
        $this->p_name_bold = (bool) ($pLayout['full_name']['bold'] ?? true);

        $this->p_id_x = (float) ($pLayout['attendance_code']['x'] ?? 50);
        $this->p_id_y = (float) ($pLayout['attendance_code']['y'] ?? 54);
        $this->p_id_size = (float) ($pLayout['attendance_code']['font_size'] ?? 14);
        $this->p_id_color = (string) ($pLayout['attendance_code']['color'] ?? '#5b21b6');
        $this->p_id_align = (string) ($pLayout['attendance_code']['align'] ?? 'center');
        $this->p_id_bold = (bool) ($pLayout['attendance_code']['bold'] ?? false);

        $this->troop_template_url = $settings->certificateTroopTemplateUrl();
        $tLayout = $settings->certificateTroopLayout();

        $this->t_name_x = (float) ($tLayout['troop_name']['x'] ?? 50);
        $this->t_name_y = (float) ($tLayout['troop_name']['y'] ?? 45);
        $this->t_name_size = (float) ($tLayout['troop_name']['font_size'] ?? 28);
        $this->t_name_color = (string) ($tLayout['troop_name']['color'] ?? '#1f1a33');
        $this->t_name_align = (string) ($tLayout['troop_name']['align'] ?? 'center');
        $this->t_name_bold = (bool) ($tLayout['troop_name']['bold'] ?? true);

        $this->t_number_x = (float) ($tLayout['troop_number']['x'] ?? 50);
        $this->t_number_y = (float) ($tLayout['troop_number']['y'] ?? 54);
        $this->t_number_size = (float) ($tLayout['troop_number']['font_size'] ?? 18);
        $this->t_number_color = (string) ($tLayout['troop_number']['color'] ?? '#5b21b6');
        $this->t_number_align = (string) ($tLayout['troop_number']['align'] ?? 'center');
        $this->t_number_bold = (bool) ($tLayout['troop_number']['bold'] ?? true);
    }

    public function saveParticipant(SettingService $settings): void
    {
        $this->validate([
            'participant_template' => 'nullable|image|max:5120',
            'p_name_x' => 'required|numeric|between:0,100',
            'p_name_y' => 'required|numeric|between:0,100',
            'p_name_size' => 'required|numeric|between:8,72',
            'p_name_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'p_name_align' => 'required|in:left,center,right',
            'p_name_bold' => 'boolean',
            'p_id_x' => 'required|numeric|between:0,100',
            'p_id_y' => 'required|numeric|between:0,100',
            'p_id_size' => 'required|numeric|between:8,72',
            'p_id_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            'p_id_align' => 'required|in:left,center,right',
            'p_id_bold' => 'boolean',
        ]);

        $data = [];

        if ($this->participant_template) {
            $oldPath = $settings->get('certificate_participant_template');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $stored = $this->participant_template->store('certificates/templates', 'public');
            $data['certificate_participant_template'] = $stored;
            $this->participant_template = null;
        }

        $data['certificate_participant_layout'] = json_encode([
            'full_name' => [
                'x' => round((float) $this->p_name_x, 2),
                'y' => round((float) $this->p_name_y, 2),
                'font_size' => round((float) $this->p_name_size, 1),
                'color' => $this->p_name_color,
                'align' => $this->p_name_align,
                'bold' => (bool) $this->p_name_bold,
            ],
            'attendance_code' => [
                'x' => round((float) $this->p_id_x, 2),
                'y' => round((float) $this->p_id_y, 2),
                'font_size' => round((float) $this->p_id_size, 1),
                'color' => $this->p_id_color,
                'align' => $this->p_id_align,
                'bold' => (bool) $this->p_id_bold,
            ],
        ]);

        $settings->save($data);
        $this->loadSettings($settings);

        $this->dispatch('toast', message: 'Pengaturan sertifikat peserta berhasil disimpan.', type: 'success');
    }

    public function deleteParticipantTemplate(SettingService $settings): void
    {
        $oldPath = $settings->get('certificate_participant_template');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $settings->save(['certificate_participant_template' => '']);
        $this->participant_template = null;
        $this->participant_template_url = null;

        $this->dispatch('toast', message: 'Template kustom sertifikat peserta dihapus. Sistem kembali ke desain bawaan.', type: 'info');
    }

    public function resetParticipantLayout(SettingService $settings): void
    {
        $defaults = $settings->defaultParticipantCertificateLayout();
        $this->p_name_x = $defaults['full_name']['x'];
        $this->p_name_y = $defaults['full_name']['y'];
        $this->p_name_size = $defaults['full_name']['font_size'];
        $this->p_name_color = $defaults['full_name']['color'];
        $this->p_name_align = $defaults['full_name']['align'];
        $this->p_name_bold = $defaults['full_name']['bold'];

        $this->p_id_x = $defaults['attendance_code']['x'];
        $this->p_id_y = $defaults['attendance_code']['y'];
        $this->p_id_size = $defaults['attendance_code']['font_size'];
        $this->p_id_color = $defaults['attendance_code']['color'];
        $this->p_id_align = $defaults['attendance_code']['align'];
        $this->p_id_bold = $defaults['attendance_code']['bold'];

        $this->dispatch('toast', message: 'Posisi teks dikembalikan ke nilai default. Klik "Simpan Perubahan" untuk menerapkan.', type: 'info');
    }

    public function saveTroop(SettingService $settings): void
    {
        $this->validate([
            'troop_template' => 'nullable|image|max:5120',
            't_name_x' => 'required|numeric|between:0,100',
            't_name_y' => 'required|numeric|between:0,100',
            't_name_size' => 'required|numeric|between:8,72',
            't_name_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            't_name_align' => 'required|in:left,center,right',
            't_name_bold' => 'boolean',
            't_number_x' => 'required|numeric|between:0,100',
            't_number_y' => 'required|numeric|between:0,100',
            't_number_size' => 'required|numeric|between:8,72',
            't_number_color' => ['required', 'regex:/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/'],
            't_number_align' => 'required|in:left,center,right',
            't_number_bold' => 'boolean',
        ]);

        $data = [];

        if ($this->troop_template) {
            $oldPath = $settings->get('certificate_troop_template');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $stored = $this->troop_template->store('certificates/templates', 'public');
            $data['certificate_troop_template'] = $stored;
            $this->troop_template = null;
        }

        $data['certificate_troop_layout'] = json_encode([
            'troop_name' => [
                'x' => round((float) $this->t_name_x, 2),
                'y' => round((float) $this->t_name_y, 2),
                'font_size' => round((float) $this->t_name_size, 1),
                'color' => $this->t_name_color,
                'align' => $this->t_name_align,
                'bold' => (bool) $this->t_name_bold,
            ],
            'troop_number' => [
                'x' => round((float) $this->t_number_x, 2),
                'y' => round((float) $this->t_number_y, 2),
                'font_size' => round((float) $this->t_number_size, 1),
                'color' => $this->t_number_color,
                'align' => $this->t_number_align,
                'bold' => (bool) $this->t_number_bold,
            ],
        ]);

        $settings->save($data);
        $this->loadSettings($settings);

        $this->dispatch('toast', message: 'Pengaturan sertifikat gugusdepan berhasil disimpan.', type: 'success');
    }

    public function deleteTroopTemplate(SettingService $settings): void
    {
        $oldPath = $settings->get('certificate_troop_template');
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $settings->save(['certificate_troop_template' => '']);
        $this->troop_template = null;
        $this->troop_template_url = null;

        $this->dispatch('toast', message: 'Template kustom sertifikat gugusdepan dihapus. Sistem kembali ke desain bawaan.', type: 'info');
    }

    public function resetTroopLayout(SettingService $settings): void
    {
        $defaults = $settings->defaultTroopCertificateLayout();
        $this->t_name_x = $defaults['troop_name']['x'];
        $this->t_name_y = $defaults['troop_name']['y'];
        $this->t_name_size = $defaults['troop_name']['font_size'];
        $this->t_name_color = $defaults['troop_name']['color'];
        $this->t_name_align = $defaults['troop_name']['align'];
        $this->t_name_bold = $defaults['troop_name']['bold'];

        $this->t_number_x = $defaults['troop_number']['x'];
        $this->t_number_y = $defaults['troop_number']['y'];
        $this->t_number_size = $defaults['troop_number']['font_size'];
        $this->t_number_color = $defaults['troop_number']['color'];
        $this->t_number_align = $defaults['troop_number']['align'];
        $this->t_number_bold = $defaults['troop_number']['bold'];

        $this->dispatch('toast', message: 'Posisi teks dikembalikan ke nilai default. Klik "Simpan Perubahan" untuk menerapkan.', type: 'info');
    }

    public function render()
    {
        return view('livewire.admin.certificates.settings');
    }
}
