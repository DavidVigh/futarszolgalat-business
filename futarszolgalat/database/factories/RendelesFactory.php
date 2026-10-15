<?php

namespace Database\Factories;

use App\Models\Futar;
use App\Models\Rendeles;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rendeles>
 */
class RendelesFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'felhasznalo_id' => User::factory(),
            'futar_id' => null,
            'cim' => fake()->address(),
            'prioritas' => false,
            'fizetesi_mod' => 'kartya',
            'jatt' => null,
            'szamla' => false,
            'km' => 5,
            'statusz' => Rendeles::STATUSZ_UJ,
        ];
    }

    /** Every order needs at least one item (BL 5). */
    public function configure(): static
    {
        return $this->afterCreating(function (Rendeles $rendeles): void {
            if (! $rendeles->tetelek()->exists()) {
                $termek = Termek::factory()->create();
                $rendeles->tetelek()->create(['termek_id' => $termek->id, 'db' => 1, 'egysegar' => $termek->ar]);
            }
        });
    }

    /** Orders a courier handles need one (DB: ck_rendeles_futar_kotelezo). */
    public function statusz(string $statusz): static
    {
        $futarNelkul = in_array($statusz, [Rendeles::STATUSZ_KOSAR, Rendeles::STATUSZ_UJ, Rendeles::STATUSZ_LEMONDVA], true);

        return $this->state(fn (array $attributes) => [
            'statusz' => $statusz,
            'futar_id' => $futarNelkul ? null : Futar::factory()->allapot(
                $statusz === Rendeles::STATUSZ_KISZALLITVA ? Futar::ALLAPOT_ELERHETO : Futar::ALLAPOT_UTON,
            ),
        ]);
    }

    public function keszpenzes(): static
    {
        return $this->state(fn (array $attributes) => ['fizetesi_mod' => 'keszpenz', 'jatt' => null]);
    }
}
