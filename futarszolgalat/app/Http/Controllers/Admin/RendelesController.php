<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rendeles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Read-only for admins: order statuses only change through the customer and
 * courier flows, every other transition is forbidden (BL 10).
 */
class RendelesController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['statusz' => ['sometimes', Rule::in([
            Rendeles::STATUSZ_UJ, Rendeles::STATUSZ_FELVEVE, Rendeles::STATUSZ_UTON, Rendeles::STATUSZ_KISZALLITVA, Rendeles::STATUSZ_LEMONDVA,
        ])]]);

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
}
