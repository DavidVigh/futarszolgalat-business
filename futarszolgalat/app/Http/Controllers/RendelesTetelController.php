<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRendelesTetelRequest;
use App\Http\Requests\UpdateRendelesTetelRequest;
use App\Models\Rendeles;
use App\Models\Termek;
use App\Services\RendelesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

/** Items of the customer's own order; only changeable while the order is new (BL 5, 6). */
class RendelesTetelController extends Controller
{
    public function store(StoreRendelesTetelRequest $request, Rendeles $rendeles, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->tetelHozzaad($rendeles, $request->integer('termek_id'), $request->integer('db', 1)));
    }

    public function update(UpdateRendelesTetelRequest $request, Rendeles $rendeles, Termek $termek, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->tetelModosit($rendeles, $termek->id, $request->integer('db')));
    }

    public function destroy(Rendeles $rendeles, Termek $termek, RendelesService $rendelesek): JsonResponse
    {
        Gate::authorize('update', $rendeles);

        return response()->json($rendelesek->tetelTorol($rendeles, $termek->id));
    }
}
