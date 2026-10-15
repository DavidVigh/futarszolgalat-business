<?php

namespace Tests\Feature;

use App\Models\Futar;
use App\Models\Rendeles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourierDeactivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_a_busy_courier_cannot_deactivate_them(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([Rendeles::STATUSZ_ELFOGADVA, Rendeles::STATUSZ_KESZUL, Rendeles::STATUSZ_UTON] as $statusz) {
            $rendeles = Rendeles::factory()->statusz($statusz)->create();
            $futar = $rendeles->futar;

            $this->actingAs($admin)->patchJson("/api/admin/futarok/{$futar->id}", ['aktiv' => false])
                ->assertUnprocessable()
                ->assertJsonPath('message', 'Aktiv rendelessel rendelkezo futar nem inaktivalhato.');
            $this->actingAs($admin)->putJson("/api/admin/futarok/{$futar->id}", ['aktiv' => 0])
                ->assertUnprocessable();

            $this->assertTrue($futar->fresh()->aktiv);
            $this->assertSame($statusz, $rendeles->fresh()->statusz);
        }
    }

    public function test_updating_an_available_courier_can_deactivate_them(): void
    {
        $futar = Futar::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patchJson("/api/admin/futarok/{$futar->id}", ['aktiv' => false])
            ->assertOk()
            ->assertJsonPath('aktiv', false);

        $this->assertFalse($futar->fresh()->aktiv);
    }

    public function test_busy_courier_details_can_still_be_updated(): void
    {
        $rendeles = Rendeles::factory()->statusz(Rendeles::STATUSZ_UTON)->create();
        $futar = $rendeles->futar;

        $this->actingAs(User::factory()->admin()->create())
            ->patchJson("/api/admin/futarok/{$futar->id}", ['nev' => 'Javitott futarnev', 'aktiv' => true])
            ->assertOk()
            ->assertJsonPath('nev', 'Javitott futarnev')
            ->assertJsonPath('aktiv', true);

        $this->assertSame('Javitott futarnev', $futar->fresh()->nev);
        $this->assertSame(Rendeles::STATUSZ_UTON, $rendeles->fresh()->statusz);
    }
}
