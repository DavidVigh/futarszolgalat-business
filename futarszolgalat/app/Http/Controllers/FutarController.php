<?php

namespace App\Http\Controllers;

use App\Http\Requests\Futar\AllapotRequest;
use App\Http\Requests\Futar\PozicioRequest;
use App\Models\Futar;
use App\Models\Rendeles;
use App\Services\FutarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The logged-in courier (session key futar_id, set by AuthController::futarLogin). */
class FutarController extends Controller
{
    public function __construct(private FutarService $futarok) {}

    public function en(Request $request): JsonResponse
    {
        return response()->json($this->authenticatedFutar($request)->load(['aktivRendeles', 'aktualisPozicio']));
    }

    /** Orders the courier can pick up right now. */
    public function rendelesek(Request $request): JsonResponse
    {
        return response()->json($this->futarok->felveheto($this->authenticatedFutar($request))->with('tetelek.termek')->paginate());
    }

    public function sajatRendelesek(Request $request): JsonResponse
    {
        $rendelesek = $this->authenticatedFutar($request)->rendelesek()
            ->with('tetelek.termek')
            ->latest('letrehozva')
            ->latest('id')
            ->paginate();

        return response()->json($rendelesek);
    }

    public function felvesz(Request $request, Rendeles $rendeles): JsonResponse
    {
        return response()->json($this->futarok->felvesz($this->authenticatedFutar($request), $rendeles));
    }

    public function atvesz(Request $request, Rendeles $rendeles): JsonResponse
    {
        return response()->json($this->futarok->atvesz($this->authenticatedFutar($request), $rendeles));
    }

    public function kiszallit(Request $request, Rendeles $rendeles): JsonResponse
    {
        return response()->json($this->futarok->kiszallit($this->authenticatedFutar($request), $rendeles));
    }

    public function allapot(AllapotRequest $request): JsonResponse
    {
        return response()->json($this->futarok->allapotValt($this->authenticatedFutar($request), $request->validated('allapot')));
    }

    public function pozicio(PozicioRequest $request): JsonResponse
    {
        return response()->json($this->futarok->pozicioRogzit($this->authenticatedFutar($request), $request->validated()), 201);
    }

    private function authenticatedFutar(Request $request): Futar
    {
        return Futar::findOrFail($request->session()->get('futar_id'));
    }
}
