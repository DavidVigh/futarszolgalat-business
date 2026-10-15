<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErtekelesRequest;
use App\Http\Requests\JattRequest;
use App\Http\Requests\StoreRendelesRequest;
use App\Models\Rendeles;
use App\Services\RendelesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** The logged-in customer's own orders. */
class RendelesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $rendelesek = $request->user()->rendelesek()
            ->with('tetelek.termek')
            ->latest('letrehozva')
            ->latest('id')
            ->paginate();

        return response()->json($rendelesek);
    }

    public function store(StoreRendelesRequest $request, RendelesService $rendelesek): JsonResponse
    {
        return response()->json($rendelesek->letrehoz($request->user(), $request->validated()), 201);
    }

    public function show(Rendeles $rendeles): JsonResponse
    {
        Gate::authorize('view', $rendeles);

        return response()->json($rendeles->load(['tetelek.termek', 'futar', 'szamlazasiAdat']));
    }

    public function lemond(Rendeles $rendeles, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->lemond($rendeles));
    }

    public function ertekel(ErtekelesRequest $request, Rendeles $rendeles, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->ertekel($rendeles, $request->integer('ertekeles')));
    }

    public function jatt(JattRequest $request, Rendeles $rendeles, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->jattot($rendeles, $request->integer('jatt')));
    }
}
