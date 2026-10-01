<?php

namespace App\Http\Controllers;

use App\Models\Futar;
use App\Models\Rendeles;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FutarController extends Controller
{
    public function rendelesek(Request $request): JsonResponse
    {
        $futar = $this->authenticatedFutar($request);
        $rendelesek = Rendeles::query()
            ->whereNull('futar_id')
            ->whereIn('statusz', ['uj', 'elerheto'])
            ->where('km', '<=', $futar->hatosugar_km)
            ->latest('prioritas')
            ->latest('id')
            ->paginate();

        return response()->json($rendelesek);
    }

    public function vallal(Request $request, Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $futar = $this->authenticatedFutar($request);
        $aktiv = Rendeles::where('futar_id', $futar->id)->whereNotIn('statusz', ['teljesitve', 'lemondva'])->count();
        abort_if($aktiv >= 5, 422, 'A futar maximum 5 aktiv rendelest vallalhat.');

        $pythonApi->call('rendeles_modositasa', [
            'id' => $rendeles->id,
            'futar_id' => $futar->id,
            'statusz' => 'uton',
        ]);

        return response()->json($rendeles->refresh());
    }

    public function sajatRendelesek(Request $request): JsonResponse
    {
        return response()->json(Rendeles::where('futar_id', $request->session()->get('futar_id'))->latest('id')->paginate());
    }

    private function authenticatedFutar(Request $request): Futar
    {
        return Futar::findOrFail($request->session()->get('futar_id'));
    }
}
