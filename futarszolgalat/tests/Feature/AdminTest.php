<?php

namespace Tests\Feature;

use App\Models\Allergen;
use App\Models\Futar;
use App\Models\Rendeles;
use App\Models\Termek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_is_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->getJson('/api/admin/termekek')->assertForbidden();
    }

    public function test_admin_creates_product_with_allergens(): void
    {
        $gluten = Allergen::factory()->create();

        $this->actingAs(User::factory()->admin()->create())->postJson('/api/admin/termekek', [
            'tipus' => 'etel',
            'nev' => 'Rantott sajt',
            'fogas' => 'foetel',
            'ar' => 2900,
            'elkeszitesi_ido' => 15,
            'allergenek' => [$gluten->kod],
        ])->assertCreated()
            ->assertJsonPath('aktiv', true)
            ->assertJsonPath('allergenek.0.kod', $gluten->kod);
    }

    public function test_food_needs_preparation_time_and_drink_needs_drink_course(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson('/api/admin/termekek', ['tipus' => 'etel', 'nev' => 'Leves', 'fogas' => 'leves', 'ar' => 900])
            ->assertJsonValidationErrors('elkeszitesi_ido');
        $this->actingAs($admin)->postJson('/api/admin/termekek', ['tipus' => 'ital', 'nev' => 'Kola', 'fogas' => 'foetel', 'ar' => 500])
            ->assertJsonValidationErrors('fogas');
        $this->actingAs($admin)->postJson('/api/admin/termekek', ['tipus' => 'etel', 'nev' => 'Leves', 'fogas' => 'nincs_ilyen', 'ar' => 900, 'elkeszitesi_ido' => 10])
            ->assertJsonValidationErrors('fogas');
    }

    public function test_ordered_product_cannot_be_deleted_only_deactivated(): void
    {
        $admin = User::factory()->admin()->create();
        $rendeles = Rendeles::factory()->create();
        $termekId = $rendeles->tetelek()->value('termek_id');

        $this->actingAs($admin)->deleteJson("/api/admin/termekek/{$termekId}")->assertUnprocessable();
        $this->actingAs($admin)->patchJson("/api/admin/termekek/{$termekId}", ['aktiv' => false])
            ->assertOk()
            ->assertJsonPath('aktiv', false);
        $this->getJson('/api/termekek')->assertJsonCount(0, 'data');
    }

    public function test_allergen_in_use_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $allergen = Allergen::factory()->create();
        Termek::factory()->create()->allergenek()->attach($allergen->kod);

        $this->actingAs($admin)->deleteJson("/api/admin/allergenek/{$allergen->kod}")->assertUnprocessable();
        $this->actingAs($admin)->postJson('/api/admin/allergenek', ['nev' => strtoupper($allergen->nev)])
            ->assertJsonValidationErrors('nev');
    }

    public function test_deleting_a_customer_deactivates_them_and_keeps_their_orders(): void
    {
        $admin = User::factory()->admin()->create();
        $rendeles = Rendeles::factory()->create();

        $this->actingAs($admin)->deleteJson("/api/admin/felhasznalok/{$rendeles->felhasznalo_id}")
            ->assertOk()
            ->assertJsonPath('aktiv', false);
        $this->assertModelExists($rendeles);

        $this->postJson('/api/auth/logout');
        $this->postJson('/api/auth/login', ['email' => $rendeles->felhasznalo->email, 'password' => 'password'])->assertUnprocessable();
    }

    public function test_deleting_a_courier_deactivates_them_and_keeps_the_order_history(): void
    {
        $admin = User::factory()->admin()->create();
        $futar = Futar::factory()->create();
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_KISZALLITVA)->create(['futar_id' => $futar->id]);

        $this->actingAs($admin)->deleteJson("/api/admin/futarok/{$futar->id}")
            ->assertOk()
            ->assertJsonPath('aktiv', false);

        $this->assertSame($futar->id, $rendeles->fresh()->futar_id);
        $this->postJson('/api/auth/futar-login', ['nev' => $futar->nev, 'telszam' => $futar->telszam])->assertUnprocessable();
    }

    public function test_courier_with_an_active_order_cannot_be_deactivated(): void
    {
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_UTON)->create();

        $this->actingAs(User::factory()->admin()->create())->deleteJson("/api/admin/futarok/{$rendeles->futar_id}")->assertUnprocessable();
    }

    public function test_there_are_no_drone_couriers(): void
    {
        $this->actingAs(User::factory()->admin()->create())->postJson('/api/admin/futarok', [
            'nev' => 'Dron 1',
            'telszam' => '+36201234567',
            'jarmu' => 'dron',
            'hatosugar_km' => 8,
        ])->assertJsonValidationErrors('jarmu');
    }

    public function test_admin_marks_an_accepted_order_as_being_prepared(): void
    {
        $admin = User::factory()->admin()->create();
        $elfogadott = Rendeles::factory()->statusz(Rendeles::STATUSZ_ELFOGADVA)->create();
        $uj = Rendeles::factory()->create();

        $this->actingAs($admin)->postJson("/api/admin/rendelesek/{$elfogadott->id}/keszul")
            ->assertOk()
            ->assertJsonPath('statusz', Rendeles::STATUSZ_KESZUL);
        $this->actingAs($admin)->postJson("/api/admin/rendelesek/{$uj->id}/keszul")->assertUnprocessable();
        $this->actingAs(User::factory()->create())->postJson("/api/admin/rendelesek/{$elfogadott->id}/keszul")->assertForbidden();
    }
}
