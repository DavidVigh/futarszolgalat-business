<?php

namespace Tests\Feature;

use App\Models\Rendeles;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendelesLeadasTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_places_order_with_items_and_price_snapshot(): void
    {
        $vasarlo = User::factory()->create(['cim' => 'Budapest, Fo utca 1.']);
        $leves = Termek::factory()->create(['ar' => 2400]);
        $ital = Termek::factory()->ital()->create(['ar' => 600]);

        $response = $this->actingAs($vasarlo)->postJson('/api/rendelesek', [
            'km' => 4,
            'fizetesi_mod' => 'kartya',
            'szamla' => true,
            'szamlazasi_adat' => ['adoszam' => '12345678-1-42'],
            'tetelek' => [
                ['termek_id' => $leves->id, 'db' => 1],
                ['termek_id' => $ital->id, 'db' => 2],
                ['termek_id' => $leves->id, 'db' => 1],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_UJ)
            ->assertJsonPath('futar_id', null)
            ->assertJsonPath('cim', 'Budapest, Fo utca 1.')
            ->assertJsonCount(2, 'tetelek')
            ->assertJsonPath('szamlazasi_adat.nev', $vasarlo->nev)
            ->assertJsonPath('szamlazasi_adat.cim', 'Budapest, Fo utca 1.')
            ->assertJsonPath('szamlazasi_adat.adoszam', '12345678-1-42');
        $this->assertDatabaseHas('rendeles_tetel', ['termek_id' => $leves->id, 'db' => 2, 'egysegar' => 2400]);

        $leves->update(['ar' => 9999]);
        $this->assertDatabaseHas('rendeles_tetel', ['termek_id' => $leves->id, 'egysegar' => 2400]);
    }

    public function test_order_needs_at_least_one_active_product(): void
    {
        $vasarlo = User::factory()->create();
        $inaktiv = Termek::factory()->inaktiv()->create();

        $this->actingAs($vasarlo)->postJson('/api/rendelesek', ['km' => 4, 'fizetesi_mod' => 'kartya', 'tetelek' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tetelek');

        $this->actingAs($vasarlo)->postJson('/api/rendelesek', [
            'km' => 4,
            'fizetesi_mod' => 'kartya',
            'tetelek' => [['termek_id' => $inaktiv->id, 'db' => 1]],
        ])->assertUnprocessable()->assertJsonValidationErrors('tetelek.0.termek_id');

        $this->assertDatabaseCount('rendeles', 0);
    }

    public function test_tip_and_billing_details_are_rejected_when_they_cannot_apply(): void
    {
        $termek = Termek::factory()->create();
        $alap = ['km' => 4, 'fizetesi_mod' => 'kartya', 'tetelek' => [['termek_id' => $termek->id, 'db' => 1]]];

        $this->actingAs(User::factory()->create())->postJson('/api/rendelesek', [...$alap, 'jatt' => 500])
            ->assertJsonValidationErrors('jatt');
        $this->actingAs(User::factory()->create())->postJson('/api/rendelesek', [...$alap, 'szamlazasi_adat' => ['nev' => 'Ceg Kft.']])
            ->assertJsonValidationErrors('szamlazasi_adat');
        $this->actingAs(User::factory()->create())->postJson('/api/rendelesek', [...$alap, 'szamla' => true, 'szamlazasi_adat' => ['adoszam' => '123']])
            ->assertJsonValidationErrors('szamlazasi_adat.adoszam');
    }

    public function test_guest_cannot_order(): void
    {
        $this->postJson('/api/rendelesek', [])->assertUnauthorized();
    }

    public function test_customer_only_sees_own_orders(): void
    {
        $vasarlo = User::factory()->create();
        $sajat = Rendeles::factory()->create(['felhasznalo_id' => $vasarlo->id]);
        $masik = Rendeles::factory()->create();

        $this->actingAs($vasarlo)->getJson('/api/rendelesek')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $sajat->id);
        $this->actingAs($vasarlo)->getJson("/api/rendelesek/{$masik->id}")->assertForbidden();
    }
}
