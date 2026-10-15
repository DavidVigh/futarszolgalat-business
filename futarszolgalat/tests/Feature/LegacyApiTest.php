<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegacyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_swagger_login_route_forwards_validated_payload(): void
    {
        config(['python-api.base_url' => 'https://python-api.test']);
        Http::fake(['python-api.test/*' => Http::response(['id' => 1])]);

        $this->postJson('/api/bejelentkezes', [
            'email' => 'teszt@example.com',
            'jelszo' => 'titkos123',
        ])->assertOk()->assertJsonPath('id', 1);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://python-api.test/bejelentkezes'
            && $request->data() === ['email' => 'teszt@example.com', 'jelszo' => 'titkos123']);
    }

    public function test_swagger_product_route_rejects_invalid_payload(): void
    {
        $this->postJson('/api/termek', [
            'nev' => 'Teszt termek',
            'ar' => -1,
        ])->assertJsonValidationErrors(['fogas', 'ar']);
    }

    public function test_swagger_routes_forward_to_the_documented_python_paths(): void
    {
        config(['python-api.base_url' => 'https://python-api.test']);
        Http::fake(['python-api.test/*' => Http::response(['ok' => true])]);

        $this->getJson('/api/futar/allapotok')->assertOk();
        $this->getJson('/api/rendeles/12/allergenek')->assertOk();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://python-api.test/futar/allapotok');
        Http::assertSent(fn ($request): bool => $request->url() === 'https://python-api.test/rendeles/12/allergenek');
    }
}
