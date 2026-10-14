<?php

namespace Tests\Feature;

use App\Enums\ScoutUnit;
use App\Livewire\Admin\Registrations\Index;
use App\Livewire\Public\Registration\Step1TroopData;
use App\Models\Troop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ScoutUnitTest extends TestCase
{
    use RefreshDatabase;

    private function fill(string $unit)
    {
        return Livewire::withoutLazyLoading()->test(Step1TroopData::class)
            ->set('troop_number', '01-083')
            ->set('troop_name', 'SMP Negeri 1 Padangsidimpuan')
            ->set('unit', $unit)
            ->set('pic_name', 'Kak Andi')
            ->set('pic_phone', '081234567890')
            ->call('save');
    }

    public function test_unit_is_required_and_must_be_valid(): void
    {
        $this->fill('')->assertHasErrors(['unit' => 'required']);
        $this->fill('siaga')->assertHasErrors('unit');

        $this->fill('penggalang')->assertHasNoErrors()->assertDispatched('step-completed');
        $this->assertSame('penggalang', session('registration.draft.troop.unit'));

        $this->fill('penegak_pandega')->assertHasNoErrors();
    }

    public function test_step_offers_both_units_with_their_labels(): void
    {
        Livewire::withoutLazyLoading()->test(Step1TroopData::class)
            ->assertSee('Penggalang')
            ->assertSee('Penegak/Pandega')
            ->assertSee(__('Ages 11–15'));
    }

    public function test_registrations_can_be_filtered_by_unit(): void
    {
        Troop::factory()->create(['troop_name' => 'SMP Contoh Satu', 'unit' => ScoutUnit::Penggalang]);
        Troop::factory()->create(['troop_name' => 'SMA Contoh Dua', 'unit' => ScoutUnit::PenegakPandega]);

        Livewire::actingAs(User::factory()->create(['is_admin' => true]))->test(Index::class)
            ->assertSee('SMP Contoh Satu')->assertSee('SMA Contoh Dua')
            ->set('unit', 'penegak_pandega')
            ->assertDontSee('SMP Contoh Satu')->assertSee('SMA Contoh Dua')
            ->assertSee('Penegak/Pandega');
    }
}
