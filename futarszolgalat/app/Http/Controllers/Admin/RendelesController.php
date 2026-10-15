<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rendeles;
use App\Services\RendelesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Admins read orders and mark them as being prepared (the kitchen's step);
 * every other status change goes through the customer and courier flows (BL 10).
 */
class RendelesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['statusz' => ['sometimes', Rule::in(Rendeles::STATUSZOK)]]);

        $rendelesek = Rendeles::query()
            ->with(['felhasznalo', 'futar'])
            ->when($request->input('statusz'), fn ($q, $statusz) => $q->where('statusz', $statusz))
            ->latest('letrehozva')
            ->latest('id')
            ->paginate();

        return response()->json($rendelesek);
    }

    public function show(Rendeles $rendeles): JsonResponse
    {
        return response()->json($rendeles->load(['felhasznalo', 'futar', 'tetelek.termek']));
    }

    public function keszul(Rendeles $rendeles, RendelesService $rendelesek): JsonResponse
    {
        return response()->json($rendelesek->keszul($rendeles));
    }
}
