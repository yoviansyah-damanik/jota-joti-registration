<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Enums\ScoutUnit;
use App\Enums\TroopStatus;
use App\Livewire\Public\Registration\Step1TroopData;
use App\Livewire\Public\Registration\Step2ParticipantUpload;
use App\Livewire\Public\Registration\Step3Documents;
use App\Livewire\Public\Registration\Step4Review;
use App\Livewire\Public\RevisionForm;
use App\Livewire\Public\StatusCheck;
use App\Livewire\Public\TermsPage;
use App\Models\Troop;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\BuildsParticipantSheet;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use BuildsParticipantSheet, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        app(SettingService::class)->save(['max_group_number' => 5, 'min_per_number' => 1, 'registration_open' => 1]);
    }

    public function test_requirements_page_and_template_are_available(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(__('Continue to Registration'));
        $this->get(route('template.participants'))->assertOk()->assertDownload('template-peserta.xlsx');
    }

    public function test_public_pages_render(): void
    {
        $this->get(route('status'))->assertOk()->assertSee(__('Check Registration Status'));
        $this->get(route('register.completed'))->assertRedirect(route('status'));

        $this->withSession(['registration.agreed' => true])
            ->get(route('register'))
            ->assertOk()
            ->assertSee(__('Registration Form'))
            ->assertSee(__('Review & Submit'));
    }

    public function test_wizard_requires_agreement(): void
    {
        $this->get(route('register'))->assertRedirect(route('home'));

        Livewire::test(TermsPage::class)->call('proceed')->assertHasErrors('agreed');
        Livewire::test(TermsPage::class)->set('agreed', true)->call('proceed')->assertRedirect(route('register'));
    }

    public function test_full_registration_flow(): void
    {
        app(\App\Services\SettingService::class)->save(['registration_fee' => 75000]);

        Livewire::withoutLazyLoading()->test(Step1TroopData::class)
            ->set('troop_number', '01-083')
            ->set('troop_name', 'Gudep 01-083 & 01-084 SMPN 1')
            ->set('unit', 'penegak_pandega')
            ->set('pic_name', 'Kak Andi')
            ->set('pic_phone', '081234567890')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('step-completed');

        Livewire::withoutLazyLoading()->test(Step2ParticipantUpload::class)
            ->set('participantFile', $this->participantSheet($this->validRows(6)))
            ->assertSet('sheetErrors', [])
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('step-completed');

        Livewire::withoutLazyLoading()->test(Step3Documents::class)
            ->set('assignmentLetter', UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'))
            ->set('paymentProof', UploadedFile::fake()->image('bukti.jpg'))
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('step-completed');

        Livewire::withoutLazyLoading()->test(Step4Review::class)
            ->set('confirmed', true)
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('register.completed'));

        $troop = Troop::with('documents')->withCount('participants')->sole();
        $this->assertSame(TroopStatus::Submitted, $troop->status);
        $this->assertSame(ScoutUnit::PenegakPandega, $troop->unit);
        $this->assertSame(6, $troop->participants_count);
        $this->assertSame(75000, $troop->fee_per_participant);
        $this->assertSame(450000, $troop->fee_total);
        $this->assertTrue(Str::isUuid($troop->id));
        $this->assertMatchesRegularExpression('/^JJ-[A-Z0-9]{6}$/', $troop->registration_code);
        $this->assertCount(3, $troop->documents);
        foreach ($troop->documents as $document) {
            Storage::disk('local')->assertExists($document->path);
        }
        $this->assertNull(session('registration.draft'));
    }

    public function test_participant_upload_rejects_too_few_participants(): void
    {
        Livewire::withoutLazyLoading()->test(Step2ParticipantUpload::class)
            ->set('participantFile', $this->participantSheet($this->validRows(3)))
            ->assertNotSet('sheetErrors', [])
            ->call('save')
            ->assertHasErrors('participantFile');
    }

    private function fillTroopStep(string $number)
    {
        return Livewire::withoutLazyLoading()->test(Step1TroopData::class)
            ->set('troop_number', $number)
            ->set('troop_name', 'Gudep')
            ->set('unit', 'penggalang')
            ->set('pic_name', 'Kak Andi')
            ->set('pic_phone', '081234567890')
            ->call('save');
    }

    public function test_troop_number_is_formatted_and_paired(): void
    {
        $this->fillTroopStep('01083')->assertHasNoErrors()->assertSet('troop_number', '01-083');
        $this->assertSame('01-083', session('registration.draft.troop.troop_number'));

        $troop = Troop::factory()->create(['troop_number' => '02-011']);
        $this->assertSame('02-012', $troop->troop_number_pair);
        $this->assertSame('02-011 & 02-012', $troop->number_label);

        $this->fillTroopStep('1-83')->assertHasErrors('troop_number');
    }

    public function test_first_troop_number_must_be_odd(): void
    {
        $this->fillTroopStep('01-084')->assertHasErrors('troop_number');
        $this->fillTroopStep('01-100')->assertHasErrors('troop_number');
        $this->fillTroopStep('01-101')->assertHasNoErrors();
    }

    public function test_registered_troop_number_cannot_register_twice(): void
    {
        Troop::factory()->create(['troop_number' => '01-083']);

        $this->fillTroopStep('01-083')->assertHasErrors('troop_number');
        $this->fillTroopStep('01-085')->assertHasNoErrors();
    }

    public function test_status_check_uses_registration_code_only(): void
    {
        $troop = Troop::factory()->create(['troop_number' => '01-083', 'registration_code' => 'JJ-ABC234']);

        Livewire::test(StatusCheck::class)
            ->set('code', 'JJ-WRONG1')
            ->call('check')
            ->assertHasErrors('code')
            ->assertSet('troopId', null);

        Livewire::test(StatusCheck::class)
            ->set('code', ' jj-abc234 ')
            ->call('check')
            ->assertHasNoErrors()
            ->assertSet('troopId', $troop->id)
            ->assertSee($troop->troop_name)
            ->assertSee('01-083 & 01-084');

        // link dari halaman selesai langsung menampilkan status
        $this->get(route('status', ['code' => 'JJ-ABC234']))->assertOk()->assertSee($troop->troop_name);
    }

    public function test_registrant_can_submit_revision(): void
    {
        $troop = Troop::factory()->create(['status' => TroopStatus::Revision, 'admin_note' => 'Bukti transfer buram']);

        Livewire::withoutLazyLoading()->test(RevisionForm::class, ['troopId' => $troop->id, 'code' => $troop->registration_code])
            ->set('paymentProof', UploadedFile::fake()->image('bukti-baru.jpg'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertDispatched('revision-submitted');

        $troop->refresh();
        $this->assertSame(TroopStatus::Submitted, $troop->status);
        $this->assertNotNull($troop->document(DocumentType::PaymentProof));
    }

    public function test_revision_form_is_forbidden_with_wrong_code(): void
    {
        $troop = Troop::factory()->create(['status' => TroopStatus::Revision]);

        Livewire::withoutLazyLoading()
            ->test(RevisionForm::class, ['troopId' => $troop->id, 'code' => 'JJ-WRONG1'])
            ->assertSee('Not Found')
            ->assertDontSee(__('Submit Revision'));
    }

    public function test_manual_participant_entry_in_step_2_without_kta(): void
    {
        Livewire::withoutLazyLoading()->test(Step2ParticipantUpload::class)
            ->call('openManualModal')
            ->assertSet('showManualModal', true)
            ->set('manualFullName', 'Ahmad Fadli')
            ->set('manualBirthDate', '2010-06-20')
            ->set('manualGender', 'L')
            ->set('manualKta', '') // NTA dikosongkan jika tidak ada
            ->call('addManualParticipant')
            ->assertHasNoErrors()
            ->assertSet('showManualModal', false)
            ->assertCount('rows', 1);
    }
}
