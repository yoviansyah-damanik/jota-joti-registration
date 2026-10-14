<?php

namespace Database\Factories;

use App\Enums\ScoutUnit;
use App\Enums\TroopStatus;
use App\Models\Troop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Troop>
 */
class TroopFactory extends Factory
{
    public function definition(): array
    {
        return [
            // nomor ganjil agar pasangan (+1) tidak bertabrakan antar data uji
            'troop_number' => sprintf('%02d-%03d', fake()->numberBetween(1, 20), fake()->unique()->numberBetween(0, 498) * 2 + 1),
            'troop_name' => 'Gugus Depan '.fake()->city(),
            'unit' => ScoutUnit::Penggalang,
            'fee_per_participant' => 50000,
            'pic_name' => fake()->name(),
            'pic_phone' => '08'.fake()->numerify('##########'),
            'pic_email' => fake()->safeEmail(),
            'registration_code' => 'JJ-'.Str::upper(Str::random(6)),
            'status' => TroopStatus::Submitted,
            'submitted_at' => now(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => [
            'status' => TroopStatus::Verified,
            'verified_at' => now(),
        ]);
    }
}
