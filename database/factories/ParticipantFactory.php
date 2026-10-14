<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Participant;
use App\Models\Troop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'troop_id' => Troop::factory(),
            'kta_number' => fake()->unique()->numerify('##.##.##.####'),
            'full_name' => fake()->name(),
            'birth_date' => fake()->dateTimeBetween('-20 years', '-10 years')->format('Y-m-d'),
            'gender' => fake()->randomElement(Gender::cases()),
        ];
    }
}
