<?php

namespace App\Http\Controllers;

use App\Http\Requests\Legacy\BejelentkezesRequest;
use App\Http\Requests\Legacy\FelhasznaloRequest;
use App\Http\Requests\Legacy\FelhasznaloUpdateRequest;
use App\Http\Requests\Legacy\FutarAllapotRequest;
use App\Http\Requests\Legacy\FutarPozicioRequest;
use App\Http\Requests\Legacy\FutarRequest;
use App\Http\Requests\Legacy\RendelesRequest;
use App\Http\Requests\Legacy\RendelesStatuszRequest;
use App\Http\Requests\Legacy\TermekRequest;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LegacyApiController extends Controller
{
    public function __construct(private PythonApiClient $pythonApi) {}

    public function bejelentkezes(BejelentkezesRequest $request): JsonResponse
    {
        return $this->forward('POST', '/bejelentkezes', payload: $request->validated());
    }

    public function felhasznaloLetrehoz(FelhasznaloRequest $request): JsonResponse
    {
        return $this->forward('POST', '/felhasznalo', payload: $request->validated(), status: 201);
    }

    public function felhasznaloLista(Request $request): JsonResponse
    {
        return response()->json($this->pythonApi->get('felhasznalok_lekerese', $request->query()));
    }

    public function felhasznaloEmail(string $email): JsonResponse
    {
        return $this->forward('GET', '/felhasznalo/email/'.rawurlencode($email));
    }

    public function felhasznaloLekerdez(int $felhasznaloId): JsonResponse
    {
        return $this->forward('GET', "/felhasznalo/{$felhasznaloId}");
    }

    public function felhasznalo(FelhasznaloUpdateRequest $request, int $felhasznaloId): JsonResponse
    {
        return $this->forward($request->method(), "/felhasznalo/{$felhasznaloId}", payload: $request->validated());
    }

    public function felhasznaloTorol(int $felhasznaloId): JsonResponse
    {
        return $this->forward('DELETE', "/felhasznalo/{$felhasznaloId}");
    }

    public function felhasznaloRendelesek(int $felhasznaloId): JsonResponse
    {
        return $this->forward('GET', "/felhasznalo/{$felhasznaloId}/rendelesek");
    }

    public function futarLetrehoz(FutarRequest $request): JsonResponse
    {
        return $this->forward('POST', '/futar', payload: $request->validated(), status: 201);
    }

    public function futarLista(Request $request): JsonResponse
    {
        return $this->forward('GET', '/futar', query: $request->query());
    }

    public function futarElerheto(Request $request): JsonResponse
    {
        return $this->forward('GET', '/futar/elerheto', query: $request->query());
    }

    public function futarAllapotok(): JsonResponse
    {
        return $this->forward('GET', '/futar/allapotok');
    }

    public function futarSzallitasraAlkalmas(Request $request): JsonResponse
    {
        return $this->forward('GET', '/futar/szallitasra-alkalmas', query: $request->query());
    }

    public function futar(int $futarId): JsonResponse
    {
        return $this->forward('GET', "/futar/{$futarId}");
    }

    public function futarAllapot(FutarAllapotRequest $request, int $futarId): JsonResponse
    {
        return $this->forward('PATCH', "/futar/{$futarId}/allapot", payload: $request->validated());
    }

    public function futarRendelesek(int $futarId): JsonResponse
    {
        return $this->forward('GET', "/futar/{$futarId}/rendelesek");
    }

    public function futarPozicio(FutarPozicioRequest $request): JsonResponse
    {
        return $this->forward('POST', '/futar/pozicio', payload: $request->validated(), status: 201);
    }

    public function futarPoziciok(int $futarId): JsonResponse
    {
        return $this->forward('GET', "/futar/{$futarId}/poziciok");
    }

    public function allergen(): JsonResponse
    {
        return $this->forward('GET', '/allergen');
    }

    public function termekLetrehoz(TermekRequest $request): JsonResponse
    {
        return $this->forward('POST', '/termek', payload: $request->validated(), status: 201);
    }

    public function termekLista(Request $request): JsonResponse
    {
        return $this->forward('GET', '/termek', query: $request->query());
    }

    public function termek(int $termekId): JsonResponse
    {
        return $this->forward('GET', "/termek/{$termekId}");
    }

    public function rendelesLetrehoz(RendelesRequest $request): JsonResponse
    {
        return $this->forward('POST', '/rendeles', payload: $request->validated(), status: 201);
    }

    public function rendelesLista(Request $request): JsonResponse
    {
        return $this->forward('GET', '/rendeles', query: $request->query());
    }

    public function rendeles(int $rendelesId): JsonResponse
    {
        return $this->forward('GET', "/rendeles/{$rendelesId}");
    }

    public function rendelesAllergenek(int $rendelesId): JsonResponse
    {
        return $this->forward('GET', "/rendeles/{$rendelesId}/allergenek");
    }

    public function rendelesStatusz(RendelesStatuszRequest $request, int $rendelesId): JsonResponse
    {
        return $this->forward('PATCH', "/rendeles/{$rendelesId}/statusz", payload: $request->validated());
    }

    public function rendelesLemondas(int $rendelesId): JsonResponse
    {
        return $this->forward('POST', "/rendeles/{$rendelesId}/lemondas");
    }

    private function forward(string $method, string $endpoint, array $query = [], ?array $payload = null, int $status = 200): JsonResponse
    {
        return response()->json($this->pythonApi->forward($method, $endpoint, $query, $payload), $status);
    }
}
