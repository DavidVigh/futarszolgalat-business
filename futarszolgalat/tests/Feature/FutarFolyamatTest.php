<?php

namespace Tests\Feature;

use App\Models\Futar;
use App\Models\Rendeles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FutarFolyamatTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_delivery_flow(): void
    {
        $futar = Futar::factory()->create();
        $rendeles = Rendeles::factory()->create();

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$rendeles->id}/felvetel")
            ->assertOk()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_FELVEVE)
            ->assertJsonPath('futar_id', $futar->id);
        $this->assertSame(Futar::ALLAPOT_UTON, $futar->fresh()->allapot);

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$rendeles->id}/atvetel")
            ->assertOk()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_UTON);

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$rendeles->id}/kiszallitas")
            ->assertOk()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_KISZALLITVA);
        $this->assertSame(Futar::ALLAPOT_ELERHETO, $futar->fresh()->allapot);
    }

    public function test_an_order_cannot_be_picked_up_twice(): void
    {
        $elso = Futar::factory()->create();
        $masodik = Futar::factory()->create();
        $rendeles = Rendeles::factory()->create();

        $this->futarkent($elso)->postJson("/api/futar/rendelesek/{$rendeles->id}/felvetel")->assertOk();
        $this->futarkent($masodik)->postJson("/api/futar/rendelesek/{$rendeles->id}/felvetel")->assertUnprocessable();

        $this->assertSame($elso->id, $rendeles->fresh()->futar_id);
        $this->assertSame(Futar::ALLAPOT_ELERHETO, $masodik->fresh()->allapot);
    }

    public function test_courier_can_only_have_one_active_order(): void
    {
        $futar = Futar::factory()->create();
        [$elso, $masodik] = Rendeles::factory(2)->create();

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$elso->id}/felvetel")->assertOk();
        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$masodik->id}/felvetel")->assertUnprocessable();

        $this->assertSame(Rendeles::STATUSZ_UJ, $masodik->fresh()->statusz);
    }

    public function test_courier_on_break_cannot_pick_up(): void
    {
        $futar = Futar::factory()->allapot(Futar::ALLAPOT_SZUNET)->create();
        $rendeles = Rendeles::factory()->create();

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$rendeles->id}/felvetel")->assertUnprocessable();
    }

    public function test_order_outside_range_cannot_be_picked_up(): void
    {
        $futar = Futar::factory()->create(['hatosugar_km' => 5]);
        $rendeles = Rendeles::factory()->create(['km' => 8]);

        $this->futarkent($futar)->postJson("/api/futar/rendelesek/{$rendeles->id}/felvetel")->assertUnprocessable();
        $this->assertSame(Futar::ALLAPOT_ELERHETO, $futar->fresh()->allapot);
    }

    public function test_drone_cannot_take_cash_order(): void
    {
        $dron = Futar::factory()->dron()->create();
        $keszpenzes = Rendeles::factory()->keszpenzes()->create();
        $kartyas = Rendeles::factory()->create();

        $this->futarkent($dron)->getJson('/api/futar/rendelesek')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $kartyas->id);
        $this->futarkent($dron)->postJson("/api/futar/rendelesek/{$keszpenzes->id}/felvetel")->assertUnprocessable();
    }

    public function test_available_orders_list_priority_first_within_range(): void
    {
        $futar = Futar::factory()->create(['hatosugar_km' => 10]);
        $normal = Rendeles::factory()->create(['km' => 3]);
        $prioritasos = Rendeles::factory()->create(['km' => 3, 'prioritas' => true]);
        Rendeles::factory()->create(['km' => 30]);
        Rendeles::factory()->statusz(Rendeles::STATUSZ_LEMONDVA)->create(['km' => 3]);

        $this->futarkent($futar)->getJson('/api/futar/rendelesek')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $prioritasos->id)
            ->assertJsonPath('data.1.id', $normal->id);
    }

    public function test_courier_cannot_deliver_someone_elses_order(): void
    {
        $futar = Futar::factory()->allapot(Futar::ALLAPOT_UTON)->create();
        $masik = Futar::factory()->allapot(Futar::ALLAPOT_UTON)->create();
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_UTON)->create(['futar_id' => $futar->id]);

        $this->futarkent($masik)->postJson("/api/futar/rendelesek/{$rendeles->id}/kiszallitas")->assertUnprocessable();
    }

    public function test_status_switch_rules(): void
    {
        $futar = Futar::factory()->create();

        $this->futarkent($futar)->patchJson('/api/futar/allapot', ['allapot' => 'szunet'])
            ->assertOk()
            ->assertJsonPath('allapot', 'szunet');
        $this->futarkent($futar)->patchJson('/api/futar/allapot', ['allapot' => 'elerheto'])
            ->assertOk()
            ->assertJsonPath('allapot', 'elerheto');
        $this->futarkent($futar)->patchJson('/api/futar/allapot', ['allapot' => 'uton'])
            ->assertJsonValidationErrors('allapot');

        $uton = Futar::factory()->allapot(Futar::ALLAPOT_UTON)->create();
        $this->futarkent($uton)->patchJson('/api/futar/allapot', ['allapot' => 'szunet'])->assertUnprocessable();
    }

    public function test_only_courier_on_the_road_records_position(): void
    {
        $pozicio = ['gps_x' => 47.4979, 'gps_y' => 19.0402, 'cim' => 'Budapest, Deak Ferenc ter'];

        $this->futarkent(Futar::factory()->create())->postJson('/api/futar/pozicio', $pozicio)->assertUnprocessable();

        $uton = Futar::factory()->allapot(Futar::ALLAPOT_UTON)->create();
        $this->futarkent($uton)->postJson('/api/futar/pozicio', $pozicio)->assertCreated();
        $this->futarkent($uton)->getJson('/api/futar/en')
            ->assertOk()
            ->assertJsonPath('aktualis_pozicio.cim', 'Budapest, Deak Ferenc ter');
    }

    public function test_guest_is_not_a_courier(): void
    {
        $this->getJson('/api/futar/rendelesek')->assertUnauthorized();
    }

    private function futarkent(Futar $futar): static
    {
        return $this->withSession(['futar_id' => $futar->id]);
    }
}
