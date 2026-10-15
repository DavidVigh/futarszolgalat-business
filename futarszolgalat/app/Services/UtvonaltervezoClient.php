<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UtvonaltervezoClient
{
    /**
     * @param  array<string, mixed>  $pontok
     * @return array<string, mixed>|null
     */
    public function tervez(array $pontok): ?array
    {
        $baseUrl = config('utvonaltervezo.base_url');
        $endpoint = config('utvonaltervezo.endpoint');

        if (! is_string($baseUrl) || $baseUrl === '' || ! is_string($endpoint) || $endpoint === '') {
            return null;
        }

        try {
            $response = $this->request($baseUrl)->post($endpoint, $pontok);
        } catch (ConnectionException $e) {
            Log::warning('Az utvonaltervezo-szolgaltatas nem erheto el.', ['hiba' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Az utvonaltervezo-szolgaltatas hibaval valaszolt.', ['statusz' => $response->status()]);

            return null;
        }

        $json = $response->json();

        return is_array($json) ? $json : null;
    }

    private function request(string $baseUrl): PendingRequest
    {
        $request = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('utvonaltervezo.timeout', 10));

        $token = config('utvonaltervezo.token');

        return is_string($token) && $token !== '' ? $request->withToken($token) : $request;
    }
}
