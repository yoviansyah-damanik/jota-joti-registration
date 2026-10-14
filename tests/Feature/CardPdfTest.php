<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Troop;
use App\Models\User;
use App\Services\GroupAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CardPdfTest extends TestCase
{
    use RefreshDatabase;

    private function verifiedTroop(int $participants = 3): Troop
    {
        $troop = Troop::factory()->verified()->create(['registration_code' => 'JJ-ABC234', 'troop_number' => '01-083']);
        Participant::factory()->count($participants)->for($troop)->sequence(fn ($s) => ['attendance_code' => 'P-AAAAAA0'.$s->index, 'group_number' => $s->index + 1])->create();

        return $troop;
    }

    private function pageCount(string $pdf): int
    {
        return preg_match_all('/\/Type\s*\/Page[^s]/', $pdf);
    }

    public function test_all_cards_are_downloaded_as_one_pdf_with_one_b3_page_each(): void
    {
        $this->verifiedTroop(3);

        $response = $this->get(route('cards.registrant.pdf', 'jj-abc234'))->assertOk();
        $pdf = $response->getContent();

        $this->assertStringStartsWith('%PDF', $pdf);
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="kartu-01-083.pdf"', $response->headers->get('Content-Disposition'));
        $this->assertSame(3, $this->pageCount($pdf));

        // B3 vertikal: 353 × 500 mm ≈ 1000.6 × 1417.3 pt (lebar < tinggi)
        preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $box);
        $this->assertEqualsWithDelta(1000.6, (float) $box[1], 1.0);
        $this->assertEqualsWithDelta(1417.3, (float) $box[2], 1.0);
    }

    public function test_a_single_card_can_be_downloaded(): void
    {
        $troop = $this->verifiedTroop(3);
        $participant = $troop->participants()->where('attendance_code', 'P-AAAAAA01')->first();

        $response = $this->get(route('cards.registrant.pdf', ['code' => 'JJ-ABC234', 'participant' => 'p-aaaaaa01']))->assertOk();

        $this->assertSame(1, $this->pageCount($response->getContent()));
        $this->assertStringContainsString(Str::slug($participant->full_name), $response->headers->get('Content-Disposition'));
    }

    public function test_unknown_or_unverified_cards_are_not_downloadable(): void
    {
        $this->verifiedTroop(2);
        Troop::factory()->create(['registration_code' => 'JJ-XYZ789']);

        $this->get(route('cards.registrant.pdf', 'JJ-XYZ789'))->assertNotFound();
        $this->get(route('cards.registrant.pdf', 'JJ-NOPE00'))->assertNotFound();
        $this->get(route('cards.registrant.pdf', ['code' => 'JJ-ABC234', 'participant' => 'P-SALAH000']))->assertNotFound();
    }

    public function test_admin_download_requires_login_and_public_download_requires_completed_groups(): void
    {
        $troop = $this->verifiedTroop(2);

        $this->get(route('admin.cards.pdf', $troop))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.cards.pdf', $troop))->assertOk();

        // halaman depan: hanya setelah semua peserta terverifikasi bernomor grup
        auth()->logout();
        app(GroupAssignmentService::class)->clear();
        $this->assertNotSame(200, $this->get(route('cards.public.pdf', $troop))->getStatusCode());

        app(GroupAssignmentService::class)->assign();
        $this->get(route('cards.public.pdf', $troop))->assertOk();
    }

    public function test_card_page_offers_print_and_pdf_buttons_per_card(): void
    {
        $this->verifiedTroop(2);

        $this->get(route('cards.registrant', 'JJ-ABC234'))
            ->assertOk()
            ->assertSee(route('cards.registrant.pdf', ['code' => 'JJ-ABC234', 'participant' => 'P-AAAAAA00']), false)
            ->assertSee(route('cards.registrant.pdf', 'JJ-ABC234'), false)
            ->assertSee(__('Download all (PDF)'))
            ->assertSee('printCard(this)', false);
    }
}
