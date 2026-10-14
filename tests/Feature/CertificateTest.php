<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Livewire\Admin\Settings;
use App\Livewire\Public\StatusCheck;
use App\Livewire\Public\TermsPage;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Models\Troop;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use RefreshDatabase;

    private function complete(): void
    {
        app(SettingService::class)->save([
            'event_completed' => true,
            'event_completed_at' => '2026-10-01',
            'certificate_signer_name' => 'Kak Contoh, S.Pd.',
        ]);
    }

    private function troopWithAttendance(): Troop
    {
        $troop = Troop::factory()->verified()->create(['registration_code' => 'JJ-ABC234']);
        $present = Participant::factory()->for($troop)->create(['full_name' => 'Ahmad Hadir', 'attendance_code' => 'P-AAAAAA01']);
        Participant::factory()->for($troop)->create(['full_name' => 'Budi Absen', 'attendance_code' => 'P-AAAAAA02']);

        $session = AttendanceSession::create(['name' => 'Hari 1', 'date' => now(), 'is_active' => true]);
        Attendance::create(['attendance_session_id' => $session->id, 'participant_id' => $present->id, 'status' => AttendanceStatus::Present]);

        return $troop;
    }

    public function test_certificates_are_blocked_until_event_is_completed(): void
    {
        $this->troopWithAttendance();

        $this->get(route('certificates.troop', 'JJ-ABC234'))->assertRedirect(route('home'))->assertSessionHas('toast');
        $this->get(route('certificates.participants', 'JJ-ABC234'))->assertRedirect(route('home'));
    }

    public function test_troop_and_attending_participant_certificates_are_downloaded_as_pdf(): void
    {
        $this->complete();
        $this->troopWithAttendance();

        $troop = $this->get(route('certificates.troop', 'jj-abc234'))->assertOk();
        $this->assertSame('application/pdf', $troop->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $troop->getContent());

        $all = $this->get(route('certificates.participants', 'JJ-ABC234'))->assertOk();
        $this->assertSame(1, preg_match_all('/\/Type\s*\/Page[^s]/', $all->getContent()));

        $this->get(route('certificates.participants', ['code' => 'JJ-ABC234', 'participant' => 'p-aaaaaa01']))->assertOk();
        $this->get(route('certificates.participants', ['code' => 'JJ-ABC234', 'participant' => 'P-AAAAAA02']))->assertNotFound();
    }

    public function test_requesting_all_participant_certificates_without_attendance_redirects_back_with_message(): void
    {
        $this->complete();
        $troop = Troop::factory()->verified()->create(['registration_code' => 'JJ-ABC234']);
        Participant::factory()->for($troop)->create(['attendance_code' => 'P-AAAAAA01']);

        $this->from(route('status'))->get(route('certificates.participants', 'JJ-ABC234'))
            ->assertRedirect(route('status'))
            ->assertSessionHas('toast');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->from(route('admin.registrations.show', $troop))
            ->get(route('admin.certificates.participants', $troop))
            ->assertRedirect(route('admin.registrations.show', $troop))
            ->assertSessionHas('toast');

        $this->get(route('certificates.participants', ['code' => 'JJ-ABC234', 'participant' => 'P-AAAAAA01']))->assertNotFound();
    }

    public function test_home_announces_certificates_only_after_event_is_completed(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Download E-Certificates');

        $this->complete();

        $this->get('/')->assertOk()
            ->assertSee(route('status'), false)
            ->assertDontSee('Participant cards are ready to print!')
            ->assertDontSee('Participant cards are not available yet.');
        Livewire::withoutLazyLoading()->test(TermsPage::class)->assertSee('E-sertifikat telah diterbitkan!');
    }

    public function test_unverified_troop_or_unknown_code_gets_no_certificate(): void
    {
        $this->complete();
        Troop::factory()->create(['registration_code' => 'JJ-XYZ789']);

        $this->get(route('certificates.troop', 'JJ-XYZ789'))->assertNotFound();
        $this->get(route('certificates.troop', 'JJ-NOPE00'))->assertNotFound();
    }

    public function test_admin_can_download_certificates_before_completion(): void
    {
        $troop = $this->troopWithAttendance();

        $this->get(route('admin.certificates.troop', $troop))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.certificates.participants', $troop))->assertOk();
    }

    public function test_settings_record_completion_date_and_require_signer(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        Livewire::actingAs($admin)->test(Settings::class)
            ->set('event_completed', true)
            ->set('certificate_signer_name', '')
            ->call('save')
            ->assertHasErrors(['certificate_signer_name']);

        Livewire::actingAs($admin)->test(Settings::class)
            ->set('event_completed', true)
            ->set('certificate_signer_name', 'Kak Contoh')
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(SettingService::class);
        $this->assertTrue($settings->eventCompleted());
        $this->assertSame(now()->toDateString(), $settings->get('event_completed_at'));
    }

    public function test_status_page_lists_certificates_after_completion(): void
    {
        $this->troopWithAttendance();

        Livewire::withoutLazyLoading()->withQueryParams(['code' => 'JJ-ABC234'])->test(StatusCheck::class)->assertDontSee('Sertifikat gudep');

        $this->complete();

        Livewire::withoutLazyLoading()->withQueryParams(['code' => 'JJ-ABC234'])->test(StatusCheck::class)
            ->assertSee(route('certificates.troop', 'JJ-ABC234'))
            ->assertSee('Ahmad Hadir')
            ->assertSee(route('certificates.participants', ['JJ-ABC234', 'P-AAAAAA01']))
            ->assertDontSee(route('certificates.participants', ['JJ-ABC234', 'P-AAAAAA02']));
    }
}
