<?php

namespace Tests\Feature;

use App\Livewire\Public\CardCenter;
use App\Models\Participant;
use App\Models\Troop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CardCenterTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hides_card_center_until_groups_are_assigned(): void
    {
        Troop::factory()->verified()->has(Participant::factory()->count(2))->create();

        $this->get('/')->assertOk()->assertDontSee('Print Participant Cards');
    }

    public function test_home_shows_troops_and_cards_after_groups_are_assigned(): void
    {
        $troop = Troop::factory()->verified()->has(Participant::factory()->count(2)->state(['group_number' => 1]))->create();

        $this->get('/')->assertOk();

        Livewire::withoutLazyLoading()->test(CardCenter::class)
            ->assertSee($troop->troop_name)
            ->call('select', $troop->id)
            ->assertSee($troop->participants->first()->full_name);

        $this->get(route('cards.public', $troop))->assertOk();
    }

    public function test_public_cards_are_blocked_while_groups_are_incomplete(): void
    {
        $troop = Troop::factory()->verified()->has(Participant::factory()->count(2))->create();

        $this->get(route('cards.public', $troop))->assertRedirect(route('home'))->assertSessionHas('toast');
        $this->get(route('cards.public.pdf', $troop))->assertRedirect(route('home'));
        $this->get(route('cards.index'))->assertRedirect(route('home'));
    }

    public function test_registrant_cards_are_blocked_until_cards_are_published(): void
    {
        $troop = Troop::factory()->verified()->has(Participant::factory()->count(2)->state(['attendance_code' => null]))->create(['registration_code' => 'JJ-ABC234']);

        $this->get(route('cards.registrant', 'JJ-ABC234'))->assertRedirect(route('home'));
        $this->get(route('cards.registrant.pdf', 'JJ-ABC234'))->assertRedirect(route('home'));

        $troop->participants()->update(['group_number' => 1]);

        $this->get(route('cards.index'))->assertOk();
    }
}
