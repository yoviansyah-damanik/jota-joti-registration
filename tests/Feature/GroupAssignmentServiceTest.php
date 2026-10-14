<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\Troop;
use App\Services\GroupAssignmentService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(SettingService::class)->save(['max_group_number' => 5, 'min_per_number' => 1]);
    }

    public function test_numbers_are_balanced_within_each_troop_and_globally(): void
    {
        $troops = collect([3, 5, 7, 12, 23])->map(fn ($size) => Troop::factory()->verified()->has(Participant::factory()->count($size))->create());

        $batch = app(GroupAssignmentService::class)->assign();

        $this->assertSame(50, $batch->total_participants);
        $this->assertSame(0, Participant::whereNull('group_number')->count());

        foreach ($troops as $troop) {
            $counts = $troop->participants()->get()->countBy('group_number');
            $this->assertLessThanOrEqual(1, $counts->max() - $counts->min(), "Troop of {$troop->participants()->count()} is not balanced");
            $this->assertTrue($counts->keys()->every(fn ($n) => $n >= 1 && $n <= 5));

            if ($troop->participants()->count() >= 5) {
                $this->assertCount(5, $counts, 'Every number must appear in a troop with at least N participants');
            }
        }

        $global = Participant::toBase()->selectRaw('group_number, count(*) as total')->groupBy('group_number')->pluck('total');
        $this->assertLessThanOrEqual(1, $global->max() - $global->min());
        $this->assertTrue($batch->summary['balanced']);
    }

    public function test_unverified_troops_are_not_assigned(): void
    {
        Troop::factory()->has(Participant::factory()->count(6))->create();
        Troop::factory()->verified()->has(Participant::factory()->count(6))->create();

        app(GroupAssignmentService::class)->assign();

        $this->assertSame(6, Participant::whereNotNull('group_number')->count());
    }

    public function test_incremental_run_keeps_existing_numbers(): void
    {
        $first = Troop::factory()->verified()->has(Participant::factory()->count(7))->create();
        app(GroupAssignmentService::class)->assign();
        $before = $first->participants()->pluck('group_number', 'id');

        Troop::factory()->verified()->has(Participant::factory()->count(8))->create();
        $batch = app(GroupAssignmentService::class)->assign();

        $this->assertSame(1, $batch->total_troops);
        $this->assertEquals($before, $first->participants()->pluck('group_number', 'id'));
        $this->assertTrue($batch->summary['balanced']);
    }

    public function test_troop_below_minimum_is_reported(): void
    {
        Troop::factory()->verified()->has(Participant::factory()->count(3))->create();

        $batch = app(GroupAssignmentService::class)->assign();

        $this->assertCount(1, $batch->summary['troop_issues']);
    }

    public function test_clear_removes_all_group_numbers(): void
    {
        Troop::factory()->verified()->has(Participant::factory()->count(6))->create();
        $service = app(GroupAssignmentService::class);
        $service->assign();
        $this->assertTrue($service->isCompleted());

        $this->assertSame(6, $service->clear());
        $this->assertSame(0, Participant::whereNotNull('group_number')->count());
        $this->assertFalse($service->isCompleted());
    }

    public function test_penggalang_and_penegak_are_strictly_separated_into_unit_blocks(): void
    {
        app(SettingService::class)->save([
            'penggalang_group_count' => 4,
            'penggalang_start_number' => 1,
            'penegak_group_count' => 4,
            'penegak_start_number' => 5,
            'min_per_number' => 1,
        ]);

        $penggalangTroop1 = Troop::factory()->verified()->create([
            'unit' => \App\Enums\ScoutUnit::Penggalang,
        ]);
        Participant::factory()->count(12)->for($penggalangTroop1)->create();

        $penggalangTroop2 = Troop::factory()->verified()->create([
            'unit' => \App\Enums\ScoutUnit::Penggalang,
        ]);
        Participant::factory()->count(8)->for($penggalangTroop2)->create();

        $penegakTroop1 = Troop::factory()->verified()->create([
            'unit' => \App\Enums\ScoutUnit::PenegakPandega,
        ]);
        Participant::factory()->count(10)->for($penegakTroop1)->create();

        $penegakTroop2 = Troop::factory()->verified()->create([
            'unit' => \App\Enums\ScoutUnit::PenegakPandega,
        ]);
        Participant::factory()->count(6)->for($penegakTroop2)->create();

        $batch = app(GroupAssignmentService::class)->assign();

        $this->assertSame(36, $batch->total_participants);

        // Penggalang peserta hanya boleh di grup 1 - 4
        $penggalangGroupNumbers = Participant::whereHas('troop', fn ($q) => $q->where('unit', \App\Enums\ScoutUnit::Penggalang))
            ->pluck('group_number')
            ->unique();
        $this->assertTrue($penggalangGroupNumbers->every(fn ($n) => in_array($n, [1, 2, 3, 4], true)));

        // Penegak peserta hanya boleh di grup 5 - 8
        $penegakGroupNumbers = Participant::whereHas('troop', fn ($q) => $q->where('unit', \App\Enums\ScoutUnit::PenegakPandega))
            ->pluck('group_number')
            ->unique();
        $this->assertTrue($penegakGroupNumbers->every(fn ($n) => in_array($n, [5, 6, 7, 8], true)));

        // Tidak boleh ada irisan nomor grup antara Penggalang dan Penegak
        $overlap = $penggalangGroupNumbers->intersect($penegakGroupNumbers);
        $this->assertTrue($overlap->isEmpty(), 'Penggalang dan Penegak tidak boleh berada dalam grup yang sama!');

        // Persebaran masing-masing golongan seimbang
        $penggalangCounts = Participant::whereHas('troop', fn ($q) => $q->where('unit', \App\Enums\ScoutUnit::Penggalang))
            ->selectRaw('group_number, count(*) as total')
            ->groupBy('group_number')
            ->pluck('total');
        $this->assertLessThanOrEqual(1, $penggalangCounts->max() - $penggalangCounts->min());

        $penegakCounts = Participant::whereHas('troop', fn ($q) => $q->where('unit', \App\Enums\ScoutUnit::PenegakPandega))
            ->selectRaw('group_number, count(*) as total')
            ->groupBy('group_number')
            ->pluck('total');
        $this->assertLessThanOrEqual(1, $penegakCounts->max() - $penegakCounts->min());
    }
}
