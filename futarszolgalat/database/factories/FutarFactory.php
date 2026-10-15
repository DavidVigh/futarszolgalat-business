<?php

namespace Database\Factories;

use App\Models\Futar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Futar>
 */
class FutarFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nev' => fake()->name(),
            'telszam' => '+3620'.fake()->unique()->numerify('#######'),
            'jarmu' => 'auto',
            'ember' => true,
            'allapot' => Futar::ALLAPOT_ELERHETO,
            'hatosugar_km' => 20,
        ];
    }

    public function dron(): static
    {
        return $this->state(fn (array $attributes) => ['jarmu' => 'dron', 'ember' => false]);
    }

    public function allapot(string $allapot): static
    {
        return $this->state(fn (array $attributes) => ['allapot' => $allapot]);
    }
}
