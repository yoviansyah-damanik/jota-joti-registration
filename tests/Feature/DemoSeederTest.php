<?php

namespace Tests\Feature;

use App\Enums\TroopStatus;
use App\Models\Participant;
use App\Models\Troop;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_is_realistic_and_repeatable(): void
    {
        Storage::fake('local');
        $this->seed(DatabaseSeeder::class);

        $this->seed(DemoSeeder::class);
        $this->seed(DemoSeeder::class);

        $this->assertSame(40, Troop::count(), 'Data demo lama harus dibersihkan saat dijalankan ulang');
        $this->assertTrue(Troop::all()->every(fn (Troop $t) => (int) substr($t->troop_number, 3) % 2 === 1));
        $this->assertSame(40, Troop::distinct()->count('registration_code'));

        foreach (TroopStatus::cases() as $status) {
            $this->assertGreaterThan(0, Troop::where('status', $status)->count(), "Tidak ada gudep berstatus {$status->value}");
        }

        $troop = Troop::verified()->with('documents')->first();
        $this->assertCount(3, $troop->documents);
        foreach ($troop->documents as $document) {
            Storage::disk('local')->assertExists($document->path);
        }

        $this->assertSame(0, Participant::fromVerifiedTroops()->whereNull('group_number')->count());
        $this->assertSame(0, Participant::whereHas('troop', fn ($q) => $q->where('status', '!=', TroopStatus::Verified))->whereNotNull('group_number')->count());
    }
}
