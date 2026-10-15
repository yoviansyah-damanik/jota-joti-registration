<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Livewire\Admin\Certificates\Settings;
use App\Models\Participant;
use App\Models\TaskSubmission;
use App\Models\Troop;
use App\Models\User;
use App\Services\CertificateService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateTemplateTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_guest_cannot_access_certificate_settings(): void
    {
        $this->get(route('admin.certificates.settings'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_certificate_settings_page(): void
    {
        $this->actingAs($this->adminUser())
            ->get(route('admin.certificates.settings'))
            ->assertOk()
            ->assertSee('Pengaturan Template Sertifikat');
    }

    public function test_admin_can_upload_participant_template_and_save_layout(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->image('template-peserta.png', 1920, 1080);

        Livewire::actingAs($admin)
            ->test(Settings::class)
            ->set('participant_template', $file)
            ->set('p_name_x', 48.5)
            ->set('p_name_y', 42.0)
            ->set('p_name_size', 32)
            ->set('p_name_color', '#123456')
            ->set('p_name_align', 'center')
            ->set('p_name_bold', true)
            ->set('p_id_x', 48.5)
            ->set('p_id_y', 55.0)
            ->set('p_id_size', 16)
            ->set('p_id_color', '#654321')
            ->set('p_id_align', 'center')
            ->set('p_id_bold', false)
            ->call('saveParticipant')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $settings = app(SettingService::class);
        $this->assertTrue($settings->hasCustomParticipantTemplate());
        $this->assertNotEmpty($settings->get('certificate_participant_template'));

        $layout = $settings->certificateParticipantLayout();
        $this->assertSame(48.5, (float) $layout['full_name']['x']);
        $this->assertSame(42.0, (float) $layout['full_name']['y']);
        $this->assertSame(32.0, (float) $layout['full_name']['font_size']);
        $this->assertSame('#123456', $layout['full_name']['color']);
        $this->assertSame(48.5, (float) $layout['attendance_code']['x']);
        $this->assertSame(55.0, (float) $layout['attendance_code']['y']);
        $this->assertSame(16.0, (float) $layout['attendance_code']['font_size']);
        $this->assertSame('#654321', $layout['attendance_code']['color']);
    }

    public function test_admin_can_upload_troop_template_and_save_layout(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->image('template-gudep.jpg', 1920, 1080);

        Livewire::actingAs($admin)
            ->test(Settings::class)
            ->set('activeTab', 'troop')
            ->set('troop_template', $file)
            ->set('t_name_x', 50.0)
            ->set('t_name_y', 40.0)
            ->set('t_name_size', 30)
            ->set('t_name_color', '#222222')
            ->set('t_name_align', 'center')
            ->set('t_name_bold', true)
            ->set('t_number_x', 50.0)
            ->set('t_number_y', 52.0)
            ->set('t_number_size', 20)
            ->set('t_number_color', '#7b1fa2')
            ->set('t_number_align', 'center')
            ->set('t_number_bold', true)
            ->call('saveTroop')
            ->assertHasNoErrors()
            ->assertDispatched('toast');

        $settings = app(SettingService::class);
        $this->assertTrue($settings->hasCustomTroopTemplate());
        $this->assertNotEmpty($settings->get('certificate_troop_template'));

        $layout = $settings->certificateTroopLayout();
        $this->assertSame(50.0, (float) $layout['troop_name']['x']);
        $this->assertSame(40.0, (float) $layout['troop_name']['y']);
        $this->assertSame(30.0, (float) $layout['troop_name']['font_size']);
        $this->assertSame(50.0, (float) $layout['troop_number']['x']);
        $this->assertSame(52.0, (float) $layout['troop_number']['y']);
    }

    public function test_admin_can_preview_sample_certificate_pdf(): void
    {
        $admin = $this->adminUser();

        $responseP = $this->actingAs($admin)
            ->get(route('admin.certificates.preview', 'participant'))
            ->assertOk();

        $this->assertSame('application/pdf', $responseP->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responseP->getContent());

        $responseT = $this->actingAs($admin)
            ->get(route('admin.certificates.preview', 'troop'))
            ->assertOk();

        $this->assertSame('application/pdf', $responseT->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $responseT->getContent());
    }

    public function test_admin_can_delete_custom_templates_and_reset_layout(): void
    {
        Storage::fake('public');
        $admin = $this->adminUser();

        $file = UploadedFile::fake()->image('template.png', 1920, 1080);

        $component = Livewire::actingAs($admin)
            ->test(Settings::class)
            ->set('participant_template', $file)
            ->call('saveParticipant');

        $settings = app(SettingService::class);
        $this->assertTrue($settings->hasCustomParticipantTemplate());

        $component->call('deleteParticipantTemplate');
        $this->assertFalse($settings->hasCustomParticipantTemplate());

        $component->call('resetParticipantLayout');
        $this->assertSame(50.0, (float) $component->get('p_name_x'));
        $this->assertSame(45.0, (float) $component->get('p_name_y'));
    }

    public function test_participant_certificate_is_only_granted_for_passed_tasks(): void
    {
        $troop = Troop::factory()->verified()->create();

        // Peserta 1: Tugas dinyatakan lulus (Completed)
        $pPassed = Participant::factory()->for($troop)->create(['full_name' => 'Lulus Ujian']);
        TaskSubmission::factory()->completed()->for($pPassed)->create();

        // Peserta 2: Tugas berstatus revisi
        $pRevision = Participant::factory()->for($troop)->create(['full_name' => 'Perlu Revisi']);
        TaskSubmission::factory()->revision()->for($pRevision)->create();

        // Peserta 3: Belum mengupload tugas sama sekali
        $pNoTask = Participant::factory()->for($troop)->create(['full_name' => 'Tanpa Tugas']);

        $service = app(CertificateService::class);
        $eligible = $service->eligibleParticipants($troop);

        $this->assertCount(1, $eligible);
        $this->assertTrue($eligible->contains('id', $pPassed->id));
        $this->assertFalse($eligible->contains('id', $pRevision->id));
        $this->assertFalse($eligible->contains('id', $pNoTask->id));
    }
}
