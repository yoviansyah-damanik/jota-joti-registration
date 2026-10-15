<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Livewire\Admin\Tasks\Index as AdminTasksIndex;
use App\Livewire\Public\Tasks\CheckStatus as PublicCheckStatus;
use App\Livewire\Public\Tasks\Submit as PublicTasksSubmit;
use App\Models\Participant;
use App\Models\TaskSubmission;
use App\Models\Troop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class TaskSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_public_task_pages_are_accessible(): void
    {
        $this->get(route('tasks.submit'))->assertOk()->assertSee(__('Form Pengiriman Tugas'));
        $this->get(route('tasks.status'))->assertOk()->assertSee(__('Cek Penilaian Tugas'));
    }

    public function test_participant_lookup_validates_attendance_code(): void
    {
        Livewire::test(PublicTasksSubmit::class)
            ->set('participant_code', 'P-INVALID9')
            ->call('lookupParticipant')
            ->assertHasErrors('participant_code');

        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-ABCD1234']);

        Livewire::test(PublicTasksSubmit::class)
            ->set('participant_code', 'P-ABCD1234')
            ->call('lookupParticipant')
            ->assertHasNoErrors()
            ->assertSet('participant.id', $participant->id);
    }

    public function test_user_can_submit_task_with_required_fields_and_pdf_files(): void
    {
        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-ABCD1234']);

        $jotaPdf = UploadedFile::fake()->create('laporan-jota.pdf', 500, 'application/pdf');
        $jotiPdf = UploadedFile::fake()->create('laporan-joti.pdf', 600, 'application/pdf');

        Livewire::test(PublicTasksSubmit::class)
            ->set('participant_code', 'P-ABCD1234')
            ->set('jid', 'YB6ZBB')
            ->set('jjid', 'JJID36064')
            ->set('jota_file', $jotaPdf)
            ->set('joti_file', $jotiPdf)
            ->set('notes', 'Laporan kegiatan sesi pagi')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('tasks.status', ['code' => 'P-ABCD1234']));

        $submission = TaskSubmission::where('participant_id', $participant->id)->first();
        $this->assertNotNull($submission);
        $this->assertSame('YB6ZBB', $submission->jid);
        $this->assertSame('JJID36064', $submission->jjid);
        $this->assertSame(TaskStatus::Pending, $submission->final_status);
        $this->assertSame(TaskStatus::Pending, $submission->jota_status);
        $this->assertSame(TaskStatus::Pending, $submission->joti_status);

        Storage::disk('local')->assertExists($submission->jota_file_path);
        Storage::disk('local')->assertExists($submission->joti_file_path);
    }

    public function test_jjid_must_start_with_jjid_prefix(): void
    {
        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-PREFIX12']);

        $jotaPdf = UploadedFile::fake()->create('laporan-jota.pdf', 100, 'application/pdf');
        $jotiPdf = UploadedFile::fake()->create('laporan-joti.pdf', 100, 'application/pdf');

        // Gagal jika JJID tidak diawali dengan JJID
        Livewire::test(PublicTasksSubmit::class)
            ->set('participant_code', 'P-PREFIX12')
            ->set('jid', 'YB6ZBB')
            ->set('jjid', '36064')
            ->set('jota_file', $jotaPdf)
            ->set('joti_file', $jotiPdf)
            ->call('submit')
            ->assertHasErrors(['jjid' => 'regex']);

        // Berhasil jika diawali JJID (meski huruf kecil jjid36064, dinormalkan ke JJID36064)
        Livewire::test(PublicTasksSubmit::class)
            ->set('participant_code', 'P-PREFIX12')
            ->set('jid', 'YB6ZBB')
            ->set('jjid', 'jjid36064')
            ->set('jota_file', $jotaPdf)
            ->set('joti_file', $jotiPdf)
            ->call('submit')
            ->assertHasNoErrors();

        $submission = TaskSubmission::where('participant_id', $participant->id)->first();
        $this->assertSame('JJID36064', $submission->jjid);
    }

    public function test_user_can_check_task_assessment_status(): void
    {
        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-ABCD1234']);

        $submission = TaskSubmission::factory()->create([
            'participant_id' => $participant->id,
            'jid' => 'YB6ZBB',
            'jjid' => 'Scout_01',
            'jota_status' => TaskStatus::Completed,
            'joti_status' => TaskStatus::Completed,
            'final_status' => TaskStatus::Completed,
            'admin_notes' => 'Kinerja sangat baik dan lengkap.',
        ]);

        Livewire::test(PublicCheckStatus::class, ['code' => 'P-ABCD1234'])
            ->assertSet('participant.id', $participant->id)
            ->assertSet('submission.id', $submission->id)
            ->assertSee($participant->full_name)
            ->assertSee('YB6ZBB')
            ->assertSee('Selesai')
            ->assertSee('Kinerja sangat baik dan lengkap.');
    }

    public function test_admin_can_view_and_assess_task_submissions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-TEST1234']);

        $submission = TaskSubmission::factory()->create([
            'participant_id' => $participant->id,
            'jid' => 'YB6TEST',
            'jjid' => 'JJ_TEST',
            'jota_status' => TaskStatus::Pending,
            'joti_status' => TaskStatus::Pending,
            'final_status' => TaskStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.tasks.index'))
            ->assertOk()
            ->assertSee('Penilaian Tugas JOTA-JOTI')
            ->assertSee($participant->full_name);

        Livewire::actingAs($admin)
            ->test(AdminTasksIndex::class)
            ->call('openAssessModal', $submission->id)
            ->assertSet('showAssessModal', true)
            ->set('jota_status', 'completed')
            ->set('jota_admin_note', 'Format log QSO JOTA valid.')
            ->set('joti_status', 'revision')
            ->set('joti_admin_note', 'Mohon lengkapi bukti chatroom ScoutLink.')
            ->call('syncFinalStatus')
            ->assertSet('final_status', 'revision')
            ->set('admin_notes', 'Sebagian tugas perlu direvisi.')
            ->call('saveAssessment')
            ->assertHasNoErrors()
            ->assertSet('showAssessModal', false);

        $submission->refresh();
        $this->assertSame(TaskStatus::Completed, $submission->jota_status);
        $this->assertSame('Format log QSO JOTA valid.', $submission->jota_admin_note);
        $this->assertSame(TaskStatus::Revision, $submission->joti_status);
        $this->assertSame('Mohon lengkapi bukti chatroom ScoutLink.', $submission->joti_admin_note);
        $this->assertSame(TaskStatus::Revision, $submission->final_status);
        $this->assertSame($admin->id, $submission->assessed_by);
        $this->assertNotNull($submission->assessed_at);
    }

    public function test_task_documents_can_be_downloaded_by_admin_or_owner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $troop = Troop::factory()->verified()->create();
        $participant = Participant::factory()->for($troop)->create(['attendance_code' => 'P-DOWNLOAD']);

        Storage::disk('local')->put('tasks/jota/test.pdf', 'dummy content jota');

        $submission = TaskSubmission::factory()->create([
            'participant_id' => $participant->id,
            'jota_file_path' => 'tasks/jota/test.pdf',
        ]);

        // Admin dapat mengunduh langsung
        $this->actingAs($admin)
            ->get(route('tasks.download', ['submission' => $submission->id, 'type' => 'jota']))
            ->assertOk()
            ->assertDownload();

        auth()->logout();

        // Tamu tanpa kode ID peserta ditolak
        $this->get(route('tasks.download', ['submission' => $submission->id, 'type' => 'jota']))
            ->assertForbidden();

        // Tamu dengan kode ID peserta yang cocok diizinkan
        $this->get(route('tasks.download', ['submission' => $submission->id, 'type' => 'jota', 'code' => 'P-DOWNLOAD']))
            ->assertOk()
            ->assertDownload();
    }
}
