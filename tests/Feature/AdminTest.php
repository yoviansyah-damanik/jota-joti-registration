<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\DocumentType;
use App\Enums\TroopStatus;
use App\Livewire\Admin\Attendance\Index as AttendanceIndex;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Groups\Details as GroupDetails;
use App\Livewire\Admin\Registrations\ParticipantTable;
use App\Livewire\Admin\Registrations\Show;
use App\Livewire\Admin\Settings;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Participant;
use App\Models\Troop;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\SettingService;
use App\Support\GroupColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_pages_are_protected(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_can_open_every_page(): void
    {
        $troop = Troop::factory()->verified()->has(Participant::factory()->count(3))->create();
        $session = AttendanceSession::create(['name' => 'Hari 1', 'date' => now()->toDateString()]);

        $this->actingAs($this->admin());

        foreach ([
            route('admin.dashboard'),
            route('admin.registrations.index'),
            route('admin.registrations.show', $troop),
            route('admin.groups'),
            route('admin.groups.details'),
            route('admin.attendance.index'),
            route('admin.attendance.sessions'),
            route('admin.settings'),
            route('admin.reports.payments'),
            route('admin.print.payments'),
            route('admin.print.attendance', $session),
            route('admin.print.attendance.manual'),
        ] as $url) {
            $this->get($url)->assertOk();
        }

        $this->get(route('admin.exports.participants'))->assertDownload();
        $this->get(route('admin.exports.payments'))->assertDownload();
        $this->get(route('admin.exports.attendance', $session))->assertDownload();
    }

    public function test_only_admin_users_can_log_in(): void
    {
        User::factory()->create(['username' => 'regular_user', 'email' => 'user@test.id']);
        $this->admin()->update(['username' => 'admin_user', 'email' => 'admin@test.id']);

        Livewire::test(Login::class)->set('username', 'regular_user')->set('password', 'password')->call('login')->assertHasErrors('username');
        Livewire::test(Login::class)->set('username', 'admin_user')->set('password', 'password')->call('login')->assertRedirect(route('admin.dashboard'));
        Livewire::test(Login::class)->set('username', 'admin@test.id')->set('password', 'password')->call('login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_verifies_and_requests_revision(): void
    {
        $admin = $this->admin();
        $troop = Troop::factory()->create();

        Livewire::actingAs($admin)->test(Show::class, ['troop' => $troop])
            ->call('review', 'revision')
            ->assertHasErrors('note')
            ->set('note', 'Surat tugas belum ditandatangani')
            ->call('review', 'revision')
            ->assertHasNoErrors();

        $this->assertSame(TroopStatus::Revision, $troop->refresh()->status);

        Livewire::actingAs($admin)->test(Show::class, ['troop' => $troop])->set('note', '')->call('review', 'verified');

        $troop->refresh();
        $this->assertSame(TroopStatus::Verified, $troop->status);
        $this->assertSame($admin->id, $troop->verified_by);
    }

    public function test_attendance_only_for_verified_participants(): void
    {
        $session = AttendanceSession::create(['name' => 'Hari 1', 'date' => now()->toDateString(), 'is_active' => true]);
        $verified = Participant::factory()->for(Troop::factory()->verified())->create(['kta_number' => 'V-1', 'attendance_code' => 'P-AAAAAAAA']);
        Participant::factory()->for(Troop::factory())->create(['kta_number' => 'U-1', 'attendance_code' => 'P-BBBBBBBB']);

        $component = Livewire::actingAs($this->admin())->test(AttendanceIndex::class)
            ->assertSet('sessionId', $session->id);

        // peserta dari gudep yang belum terverifikasi ditolak
        $component->set('code', 'P-BBBBBBBB')->call('checkIn')->assertSet('lastCheckIn.status', 'error');
        $component->set('code', 'U-1')->call('checkIn')->assertSet('lastCheckIn.status', 'error');
        $this->assertSame(0, Attendance::count());

        // scan QR (ID peserta), lalu scan ulang tidak menggandakan catatan
        $component->call('scan', 'p-aaaaaaaa')
            ->assertSet('lastCheckIn.status', 'success')
            ->assertDispatched('attendance-updated')
            ->assertDispatched('scan-result', status: 'success');
        $component->call('scan', 'P-AAAAAAAA')->assertSet('lastCheckIn.status', 'duplicate');

        $this->assertSame(1, Attendance::count());
        $this->assertSame(AttendanceStatus::Present, Attendance::sole()->status);
        $this->assertSame($verified->id, Attendance::sole()->participant_id);

        $recap = app(AttendanceService::class)->recap($session);
        $this->assertSame(1, $recap['total']);
        $this->assertSame(1, $recap['counts']['present']);
    }

    public function test_scan_keeps_first_check_in_time_and_accepts_kta_as_fallback(): void
    {
        $session = AttendanceSession::create(['name' => 'Hari 1', 'date' => now()->toDateString(), 'is_active' => true]);
        Participant::factory()->for(Troop::factory()->verified())->create(['kta_number' => 'V-1', 'attendance_code' => 'P-AAAAAAAA']);

        $this->travelTo(now()->setTime(7, 30));
        Livewire::actingAs($this->admin())->test(AttendanceIndex::class)->call('scan', 'V-1')->assertSet('lastCheckIn.status', 'success');

        $this->travelTo(now()->setTime(9, 0));
        Livewire::actingAs($this->admin())->test(AttendanceIndex::class)->call('scan', 'P-AAAAAAAA')->assertSet('lastCheckIn.status', 'duplicate');

        $this->assertSame('07:30', Attendance::sole()->checked_in_at->format('H:i'));
    }

    public function test_participant_ids_are_issued_on_verification_and_stay_stable(): void
    {
        $admin = $this->admin();
        $troop = Troop::factory()->has(Participant::factory()->count(6))->create();
        $this->assertSame(0, Participant::whereNotNull('attendance_code')->count());

        Livewire::actingAs($admin)->test(Show::class, ['troop' => $troop])->call('review', 'verified');

        $codes = $troop->participants()->pluck('attendance_code');
        $this->assertCount(6, $codes->filter());
        $this->assertCount(6, $codes->unique());
        $this->assertTrue($codes->every(fn ($code) => preg_match('/^P-[A-Z2-9]{8}$/', $code)));

        // verifikasi ulang setelah revisi tidak mengganti ID yang sudah dicetak
        Livewire::actingAs($admin)->test(Show::class, ['troop' => $troop])->set('note', 'cek ulang')->call('review', 'revision');
        Livewire::actingAs($admin)->test(Show::class, ['troop' => $troop])->set('note', '')->call('review', 'verified');

        $this->assertEquals($codes->sort()->values(), $troop->participants()->pluck('attendance_code')->sort()->values());
    }

    public function test_participant_cards_are_only_available_for_verified_troops(): void
    {
        $verified = Troop::factory()->verified()->create(['registration_code' => 'JJ-ABC234']);
        Participant::factory()->for($verified)->create(['full_name' => 'Ahmad Harahap', 'attendance_code' => 'P-ZZZZZZZZ', 'group_number' => 2]);
        $pending = Troop::factory()->create(['registration_code' => 'JJ-XYZ789']);

        $this->get(route('cards.registrant', 'jj-abc234'))
            ->assertOk()
            ->assertSee('Ahmad Harahap')
            ->assertSee('P-ZZZZZZZZ')
            ->assertSee('<svg', false)
            // warna pita kartu mengikuti grup, dan ukuran kertas B3 vertikal (lebar < tinggi)
            ->assertSee('background-color: '.GroupColor::for(2)['background'], false)
            ->assertSee('size: 353mm 500mm', false);
        $this->get(route('cards.registrant', 'JJ-XYZ789'))->assertNotFound();
        $this->get(route('cards.registrant', 'JJ-NOPE00'))->assertNotFound();

        $this->get(route('admin.cards.show', $verified))->assertRedirect(route('admin.login'));
        $this->actingAs($this->admin())->get(route('admin.cards.show', $verified))->assertOk()->assertSee('P-ZZZZZZZZ');
        $this->get(route('admin.cards.show', $pending))->assertNotFound();
    }

    public function test_admin_can_add_participant_without_kta(): void
    {
        $troop = Troop::factory()->verified()->create();

        Livewire::actingAs($this->admin())
            ->test(ParticipantTable::class, ['troopId' => $troop->id])
            ->call('openAddModal')
            ->assertSet('showAddModal', true)
            ->set('full_name', 'Rizky Pratama')
            ->set('birth_date', '2010-05-15')
            ->set('gender', 'L')
            ->set('kta_number', '') // kosongkan jika tidak ada
            ->call('addParticipant')
            ->assertHasNoErrors()
            ->assertSet('showAddModal', false)
            ->assertDispatched('troop-participants-updated');

        $participant = Participant::where('full_name', 'Rizky Pratama')->sole();
        $this->assertSame($troop->id, $participant->troop_id);
        $this->assertNull($participant->kta_number);
        $this->assertSame('2010-05-15', $participant->birth_date->toDateString());
        // karena troop terverifikasi, ID peserta langsung diterbitkan
        $this->assertNotNull($participant->attendance_code);
    }

    public function test_admin_can_delete_participant(): void
    {
        $troop = Troop::factory()->create();
        $participant = Participant::factory()->for($troop)->create(['full_name' => 'Peserta Dihapus']);

        Livewire::actingAs($this->admin())
            ->test(ParticipantTable::class, ['troopId' => $troop->id])
            ->call('deleteParticipant', $participant->id)
            ->assertDispatched('troop-participants-updated');

        $this->assertDatabaseMissing('participants', ['id' => $participant->id]);
    }

    public function test_sensitive_documents_and_participants_use_uuid(): void
    {
        Storage::fake('local');
        $troop = Troop::factory()->create();

        // 1. Participant ID is a UUID
        $participant = Participant::factory()->for($troop)->create(['full_name' => 'Peserta Tes UUID']);
        $this->assertTrue(Str::isUuid($participant->id));

        // 2. TroopDocument (bukti pembayaran & dokumen lain) ID is a UUID
        $path = 'troops/'.$troop->id.'/payment_proof-'.(string) Str::uuid().'.jpg';
        Storage::disk('local')->put($path, 'dummy image content');

        $document = $troop->documents()->create([
            'type' => DocumentType::PaymentProof,
            'path' => $path,
            'original_name' => 'bukti-bayar.jpg',
            'mime' => 'image/jpeg',
            'size' => 1234,
        ]);

        $this->assertTrue(Str::isUuid($document->id));

        // Admin can download/view using the UUID
        $this->actingAs($this->admin())
            ->get(route('admin.documents.show', $document))
            ->assertOk();
    }

    public function test_group_details_page_lists_and_filters_participants_and_matrix(): void
    {
        $admin = $this->admin();
        $troop = Troop::factory()->verified()->create(['troop_name' => 'SMP Negeri 1 Padangsidimpuan']);

        $p1 = Participant::factory()->for($troop)->create([
            'full_name' => 'Ahmad Harahap',
            'group_number' => 1,
        ]);
        $p2 = Participant::factory()->for($troop)->create([
            'full_name' => 'Nurul Hasibuan',
            'group_number' => 2,
        ]);
        $p3 = Participant::factory()->for($troop)->create([
            'full_name' => 'Budi Siregar',
            'group_number' => null,
        ]);

        // Test Livewire component
        Livewire::actingAs($admin)->test(GroupDetails::class)
            ->assertOk()
            ->assertSee('Ahmad Harahap')
            ->assertSee('Nurul Hasibuan')
            ->assertSee('Budi Siregar')
            // Filter by Group 1
            ->set('group', '1')
            ->assertSee('Ahmad Harahap')
            ->assertDontSee('Nurul Hasibuan')
            ->assertDontSee('Budi Siregar')
            // Filter by Unassigned
            ->set('group', 'unassigned')
            ->assertSee('Budi Siregar')
            ->assertDontSee('Ahmad Harahap')
            // Search filter
            ->set('group', '')
            ->set('search', 'Ahmad')
            ->assertSee('Ahmad Harahap')
            ->assertDontSee('Nurul Hasibuan')
            // Switch to Matrix view mode
            ->set('search', '')
            ->call('setViewMode', 'matrix')
            ->assertSee('SMP Negeri 1 Padangsidimpuan')
            ->assertSee(__('Troop Distribution Matrix'));
    }

    public function test_admin_can_upload_and_reset_official_logo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $file = UploadedFile::fake()->image('official-logo.png', 400, 400);

        Livewire::actingAs($admin)->test(Settings::class)
            ->set('logo', $file)
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(SettingService::class);
        $path = $settings->get('official_logo_path');
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
        $this->assertTrue($settings->hasCustomLogo());

        // Test delete / reset logo
        Livewire::actingAs($admin)->test(Settings::class)
            ->call('deleteLogo')
            ->assertHasNoErrors();

        $this->assertEmpty(app(SettingService::class)->get('official_logo_path'));
        Storage::disk('public')->assertMissing($path);
        $this->assertFalse(app(SettingService::class)->hasCustomLogo());
    }

    public function test_official_logo_can_be_downloaded_and_terms_page_shows_section(): void
    {
        // 1. Halaman utama menampilkan section Official Logo
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('Official Logo'))
            ->assertSee(route('logo.download'));

        // 2. Download logo resmi
        $this->get(route('logo.download'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_admin_can_configure_group_blocking_settings(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(Settings::class)
            ->set('penggalang_group_count', 4)
            ->set('penggalang_start_number', 1)
            ->set('penegak_group_count', 4)
            ->set('penegak_start_number', 6)
            ->set('min_per_number', 2)
            ->call('save')
            ->assertHasNoErrors();

        $settings = app(SettingService::class);
        $this->assertSame(4, $settings->penggalangGroupCount());
        $this->assertSame(4, $settings->penegakGroupCount());
        $this->assertSame(1, $settings->penggalangStartNumber());
        $this->assertSame(6, $settings->penegakStartNumber());
        $this->assertSame([1, 2, 3, 4], $settings->penggalangGroups());
        $this->assertSame([6, 7, 8, 9], $settings->penegakGroups());
        $this->assertSame(9, $settings->maxGroupNumber());
    }

    public function test_admin_can_view_manual_attendance_sheets_by_group_and_by_troop(): void
    {
        $admin = $this->admin();

        $troop = Troop::factory()->verified()->create([
            'troop_name' => 'Gudep SMP 1 Test',
            'unit' => \App\Enums\ScoutUnit::Penggalang,
        ]);
        $participant = Participant::factory()->create([
            'troop_id' => $troop->id,
            'full_name' => 'Budi Santoso',
            'group_number' => 1,
        ]);

        $session = AttendanceSession::create([
            'name' => 'Sesi 1 Morse',
            'date' => now()->toDateString(),
            'is_active' => true,
        ]);

        // 1. Group By Group Number
        $this->actingAs($admin)
            ->get(route('admin.print.attendance.manual', ['group_by' => 'group', 'group_number' => 1]))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Gudep SMP 1 Test');

        // 2. Group By Troop
        $this->actingAs($admin)
            ->get(route('admin.print.attendance.manual', ['group_by' => 'troop', 'troop_id' => $troop->id]))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('Gudep SMP 1 Test');

        // 3. With Session filter
        $this->actingAs($admin)
            ->get(route('admin.print.attendance.manual', ['session_id' => $session->id]))
            ->assertOk()
            ->assertSee('Sesi 1 Morse');
    }
}
