<?php

namespace App\Http\Controllers;

use App\Models\Futar;
use App\Models\Rendeles;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FutarController extends Controller
{
    /** Orders assigned to the courier that are still waiting for pickup. */
    public function rendelesek(Request $request): JsonResponse
    {
        $futar = $this->authenticatedFutar($request);
        $rendelesek = $futar->rendelesek()
            ->with('termekek')
            ->whereIn('statusz', Rendeles::FELVETELRE_VAR)
            ->latest('prioritas')
            ->latest('id')
            ->paginate();

        return response()->json($rendelesek);
    }

    public function vallal(Request $request, Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $futar = $this->authenticatedFutar($request);
        abort_unless($rendeles->futar_id === $futar->id, 403, 'Ez a rendeles nem ehhez a futarhoz tartozik.');
        abort_unless(in_array($rendeles->statusz, Rendeles::FELVETELRE_VAR, true), 422, 'A rendeles mar nem vallalhato.');

        $uton = $futar->rendelesek()->where('statusz', 'úton')->count();
        abort_if($uton >= 5, 422, 'A futar maximum 5 aktiv rendelest vallalhat.');

        $pythonApi->call('rendeles_modositasa', [
            'id' => $rendeles->id,
            'statusz' => 'úton',
        ]);

        return response()->json($rendeles->refresh());
    }

    public function sajatRendelesek(Request $request): JsonResponse
    {
        $futar = $this->authenticatedFutar($request);

        return response()->json($futar->rendelesek()->with('termekek')->latest('id')->paginate());
    }

    private function authenticatedFutar(Request $request): Futar
    {
        return Futar::findOrFail($request->session()->get('futar_id'));
    }
}
