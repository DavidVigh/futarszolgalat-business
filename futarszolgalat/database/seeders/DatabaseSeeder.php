<?php

namespace Database\Seeders;

use App\Models\Allergen;
use App\Models\Futar;
use App\Models\Rendeles;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Small demo data set for an empty database. The full test data set lives in
 * the DB team's export (etelrendeles_javitott.db.sql).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'nev' => 'Admin',
            'email' => 'admin@example.com',
        ]);
        $vasarlok = User::factory(3)->create();

        $allergenek = collect(['Glutén', 'Laktóz', 'Tojás'])->map(fn (string $nev) => Allergen::create(['nev' => $nev]));
        Termek::factory(10)->create()->each(
            fn (Termek $termek) => $termek->allergenek()->attach($allergenek->random()->kod),
        );
        Termek::factory(4)->ital()->create();

        Futar::factory(4)->create();
        Futar::factory()->create(['jarmu' => 'bicikli', 'hatosugar_km' => 6]);

        $vasarlok->each(fn (User $vasarlo) => Rendeles::factory(2)->create(['felhasznalo_id' => $vasarlo->id, 'cim' => $vasarlo->cim]));
    }
}
