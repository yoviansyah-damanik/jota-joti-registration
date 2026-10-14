<?php

namespace Tests\Feature;

use App\Enums\TroopStatus;
use App\Livewire\Admin\Reports\PaymentSummary;
use App\Livewire\Admin\Reports\PaymentTable;
use App\Models\Participant;
use App\Models\Troop;
use App\Models\User;
use App\Services\PaymentReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentReportTest extends TestCase
{
    use RefreshDatabase;

    private function troop(int $participants, int $fee, TroopStatus $status, ?string $date = null): Troop
    {
        return Troop::factory()
            ->has(Participant::factory()->count($participants))
            ->create([
                'fee_per_participant' => $fee,
                'status' => $status,
                'submitted_at' => $date ?? now(),
                'verified_at' => $status === TroopStatus::Verified ? ($date ?? now()) : null,
            ]);
    }

    public function test_summary_uses_fee_snapshot_per_participant(): void
    {
        $this->troop(5, 50000, TroopStatus::Verified);
        $this->troop(3, 40000, TroopStatus::Submitted);
        $this->troop(2, 99000, TroopStatus::Rejected);

        $summary = app(PaymentReportService::class)->summary();

        $this->assertSame(250000, $summary['received']);
        $this->assertSame(5, $summary['received_participants']);
        $this->assertSame(120000, $summary['pending']);
        $this->assertSame(370000, $summary['expected']);
        $this->assertSame(1, $summary['rejected_troops']);
    }

    public function test_period_and_scope_filters(): void
    {
        $old = $this->troop(4, 50000, TroopStatus::Verified, '2026-03-10 09:00:00');
        $recent = $this->troop(2, 50000, TroopStatus::Verified, '2026-05-20 09:00:00');
        $pending = $this->troop(1, 50000, TroopStatus::Revision, '2026-05-21 09:00:00');

        $service = app(PaymentReportService::class);

        $ids = fn (array $filters) => $service->query($filters)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$old->id, $recent->id], $ids(['scope' => 'received']));
        $this->assertSame([$recent->id], $ids(['scope' => 'received', 'from' => '2026-05-01', 'to' => '2026-05-31']));
        $this->assertSame([$pending->id], $ids(['scope' => 'pending']));
        $this->assertSame(200000, $service->summary(['to' => '2026-04-01'])['received']);
    }

    public function test_livewire_components_render_figures(): void
    {
        $this->troop(5, 50000, TroopStatus::Verified);

        Livewire::withoutLazyLoading()->test(PaymentSummary::class)
            ->assertSee('Rp 250.000');

        Livewire::withoutLazyLoading()->test(PaymentTable::class, ['scope' => 'received'])
            ->assertSee('Rp 50.000')
            ->assertSee('Rp 250.000');
    }

    public function test_report_is_admin_only(): void
    {
        $this->get(route('admin.reports.payments'))->assertRedirect(route('admin.login'));
        $this->actingAs(User::factory()->create())->get(route('admin.reports.payments'))->assertForbidden();
    }
}
