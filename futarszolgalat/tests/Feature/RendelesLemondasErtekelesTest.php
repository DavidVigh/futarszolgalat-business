<?php

namespace Tests\Feature;

use App\Models\Futar;
use App\Models\Rendeles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendelesLemondasErtekelesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_cancel_own_new_order(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->create(['felhasznalo_id' => $vasarlo->id]);

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/lemondas")
            ->assertOk()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_LEMONDVA);
    }

    public function test_picked_up_order_cannot_be_cancelled(): void
    {
        $vasarlo = User::factory()->create();
        $futar = Futar::factory()->allapot(Futar::ALLAPOT_UTON)->create();
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_FELVEVE)->create(['felhasznalo_id' => $vasarlo->id, 'futar_id' => $futar->id]);

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/lemondas")->assertUnprocessable();
    }

    public function test_only_the_customer_can_cancel(): void
    {
        $rendeles = Rendeles::factory()->create();

        $this->actingAs(User::factory()->create())->postJson("/api/rendelesek/{$rendeles->id}/lemondas")->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->postJson("/api/rendelesek/{$rendeles->id}/lemondas")->assertForbidden();
    }

    public function test_delivered_order_can_be_rated_once(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_KISZALLITVA)->create(['felhasznalo_id' => $vasarlo->id]);

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/ertekeles", ['ertekeles' => 5])
            ->assertOk()
            ->assertJsonPath('ertekeles', 5);
        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/ertekeles", ['ertekeles' => 1])
            ->assertUnprocessable();
    }

    public function test_undelivered_order_cannot_be_rated(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->create(['felhasznalo_id' => $vasarlo->id]);

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/ertekeles", ['ertekeles' => 4])->assertUnprocessable();
        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/ertekeles", ['ertekeles' => 6])
            ->assertJsonValidationErrors('ertekeles');
    }
}
