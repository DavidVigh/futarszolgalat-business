<?php

namespace Tests\Feature;

use App\Models\Rendeles;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RendelesTetelTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_an_existing_product_increases_its_quantity(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->create(['felhasznalo_id' => $vasarlo->id]);
        $termekId = $rendeles->tetelek()->value('termek_id');

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/tetelek", ['termek_id' => $termekId, 'db' => 2])
            ->assertOk();

        $this->assertDatabaseHas('rendeles_tetel', ['rendeles_id' => $rendeles->id, 'termek_id' => $termekId, 'db' => 3]);
    }

    public function test_items_can_only_change_while_the_order_is_new(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_LEMONDVA)->create(['felhasznalo_id' => $vasarlo->id]);
        $termekId = $rendeles->tetelek()->value('termek_id');

        $this->actingAs($vasarlo)->patchJson("/api/rendelesek/{$rendeles->id}/tetelek/{$termekId}", ['db' => 5])
            ->assertUnprocessable();
    }

    public function test_last_item_cannot_be_deleted(): void
    {
        $vasarlo = User::factory()->create();
        $rendeles = Rendeles::factory()->create(['felhasznalo_id' => $vasarlo->id]);
        $elso = $rendeles->tetelek()->value('termek_id');
        $masodik = Termek::factory()->create();

        $this->actingAs($vasarlo)->deleteJson("/api/rendelesek/{$rendeles->id}/tetelek/{$elso}")
            ->assertUnprocessable();

        $this->actingAs($vasarlo)->postJson("/api/rendelesek/{$rendeles->id}/tetelek", ['termek_id' => $masodik->id])->assertOk();
        $this->actingAs($vasarlo)->deleteJson("/api/rendelesek/{$rendeles->id}/tetelek/{$elso}")
            ->assertOk()
            ->assertJsonCount(1, 'tetelek');
    }

    public function test_other_customer_cannot_change_items(): void
    {
        $rendeles = Rendeles::factory()->create();
        $termek = Termek::factory()->create();

        $this->actingAs(User::factory()->create())->postJson("/api/rendelesek/{$rendeles->id}/tetelek", ['termek_id' => $termek->id])
            ->assertForbidden();
    }
}
