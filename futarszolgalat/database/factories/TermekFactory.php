<?php

namespace Database\Factories;

use App\Models\Termek;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Termek>
 */
class TermekFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tipus' => 'etel',
            'nev' => ucfirst(fake()->unique()->words(3, true)),
            'fogas' => 'foetel',
            'leiras' => fake()->sentence(),
            'ar' => fake()->numberBetween(5, 50) * 100,
            'elkeszitesi_ido' => fake()->numberBetween(5, 40),
            'prioritas' => 0,
            'aktiv' => true,
        ];
    }

    public function ital(): static
    {
        return $this->state(fn (array $attributes) => ['tipus' => 'ital', 'fogas' => 'hideg_ital', 'elkeszitesi_ido' => null]);
    }

    public function inaktiv(): static
    {
        return $this->state(fn (array $attributes) => ['aktiv' => false]);
    }
}
