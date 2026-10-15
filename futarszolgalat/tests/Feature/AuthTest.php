<?php

namespace Tests\Feature;

use App\Models\Futar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_registers_and_logs_in(): void
    {
        $this->postJson('/api/auth/register', [
            'nev' => 'Teszt Elek',
            'cim' => 'Budapest, Fo utca 1.',
            'telszam' => '+36301234567',
            'email' => 'teszt@example.com',
            'password' => 'titkos123',
            'password_confirmation' => 'titkos123',
        ])->assertCreated()
            ->assertJsonMissingPath('user.password');
        $this->assertAuthenticated();

        $this->postJson('/api/auth/logout')->assertOk();
        $this->postJson('/api/auth/login', ['email' => 'teszt@example.com', 'password' => 'titkos123'])->assertOk();
        $this->postJson('/api/auth/login', ['email' => 'teszt@example.com', 'password' => 'rossz'])->assertUnprocessable();
    }

    public function test_phone_number_format_is_validated(): void
    {
        $this->postJson('/api/auth/register', [
            'nev' => 'Teszt Elek',
            'cim' => 'Budapest',
            'telszam' => '30 123 4567',
            'email' => 'teszt@example.com',
            'password' => 'titkos123',
            'password_confirmation' => 'titkos123',
        ])->assertJsonValidationErrors('telszam');
    }

    public function test_email_is_unique_regardless_of_case(): void
    {
        User::factory()->create(['email' => 'teszt@example.com']);

        $this->postJson('/api/auth/register', [
            'nev' => 'Masik',
            'cim' => 'Budapest',
            'telszam' => '+36301234567',
            'email' => 'TESZT@example.com',
            'password' => 'titkos123',
            'password_confirmation' => 'titkos123',
        ])->assertJsonValidationErrors('email');
    }

    public function test_plaintext_password_from_an_old_export_is_hashed_on_first_login(): void
    {
        $user = User::factory()->create(['email' => 'regi@example.com']);
        DB::table('felhasznalo')->where('id', $user->id)->update(['password' => 'regijelszo']);

        $this->postJson('/api/auth/login', ['email' => 'regi@example.com', 'password' => 'rossz'])->assertUnprocessable();
        $this->postJson('/api/auth/login', ['email' => 'regi@example.com', 'password' => 'regijelszo'])->assertOk();

        $tarolt = DB::table('felhasznalo')->where('id', $user->id)->value('password');
        $this->assertNotSame('regijelszo', $tarolt);
        $this->assertTrue(Hash::check('regijelszo', $tarolt));
    }

    public function test_courier_logs_in_with_name_and_phone(): void
    {
        $futar = Futar::factory()->create();

        $this->postJson('/api/auth/futar-login', ['nev' => $futar->nev, 'telszam' => $futar->telszam])
            ->assertOk()
            ->assertSessionHas('futar_id', $futar->id);
    }
}
