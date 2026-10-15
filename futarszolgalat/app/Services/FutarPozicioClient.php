<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads a courier's current position through the Python API, which serves the
 * MongoDB team's position data. A missing position or an unreachable API yields null.
 */
class FutarPozicioClient
{
    /**
     * Accepted response shapes, optionally wrapped in "data": the Python API's
     * {"gps_x": lat, "gps_y": lng, "cim": .., "frissitve": ..}, GeoJSON
     * {"type": "Point", "coordinates": [lng, lat]} (MongoDB's native order),
     * {"location": <GeoJSON>}, or plain {"lat": .., "lng": ..}.
     *
     * @return array{lat: float, lng: float, frissitve: string|null}|null
     */
    public function aktualis(int $futarId): ?array
    {
        $baseUrl = config('futar-pozicio.base_url');
        if (! is_string($baseUrl) || $baseUrl === '') {
            return null;
        }

        try {
            $response = $this->request($baseUrl)->get(str_replace('{id}', (string) $futarId, (string) config('futar-pozicio.endpoint')));
        } catch (ConnectionException $e) {
            Log::warning('A futarpozicio-szolgaltatas nem erheto el.', ['futar_id' => $futarId, 'hiba' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $json = $response->json();
        $adat = is_array($json) && is_array($json['data'] ?? null) ? $json['data'] : $json;

        return is_array($adat) ? $this->normalizal($adat) : null;
    }

    /**
     * @param  array<string, mixed>  $adat
     * @return array{lat: float, lng: float, frissitve: string|null}|null
     */
    private function normalizal(array $adat): ?array
    {
        $pont = is_array($adat['location'] ?? null) ? $adat['location'] : $adat;
        $koordinatak = $pont['coordinates'] ?? null;

        [$lng, $lat] = is_array($koordinatak) && count($koordinatak) === 2
            ? array_values($koordinatak)
            : [$pont['lng'] ?? $pont['gps_y'] ?? null, $pont['lat'] ?? $pont['gps_x'] ?? null];

        if (! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }

        $frissitve = $adat['frissitve'] ?? $adat['updatedAt'] ?? $adat['timestamp'] ?? null;

        return ['lat' => (float) $lat, 'lng' => (float) $lng, 'frissitve' => is_string($frissitve) ? $frissitve : null];
    }

    private function request(string $baseUrl): PendingRequest
    {
        $request = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->timeout((int) config('futar-pozicio.timeout', 5));

        $token = config('futar-pozicio.token');

        return is_string($token) && $token !== '' ? $request->withToken($token) : $request;
    }
}
