<?php

namespace App\Http\Controllers;

use App\Http\Requests\TermekIndexRequest;
use App\Models\Termek;
use Illuminate\Http\JsonResponse;

class TermekController extends Controller
{
    /** Active products only, priority products first, with their allergens (BL 7, 8). */
    public function index(TermekIndexRequest $request): JsonResponse
    {
        $szurok = $request->validated();
        $query = Termek::query()->aktivak()->with('allergenek');

        $query->when($szurok['tipus'] ?? null, fn ($q, $tipus) => $q->whereIn('tipus', $tipus));
        $query->when($szurok['fogas'] ?? null, fn ($q, $fogas) => $q->whereIn('fogas', $fogas));
        $query->when($szurok['allergen_nelkul'] ?? null, fn ($q, $kodok) => $q->whereDoesntHave('allergenek', fn ($q) => $q->whereIn('allergen.kod', $kodok)));
        $query->when($szurok['kereses'] ?? null, fn ($q, $kereses) => $q->where(fn ($q) => $q->where('nev', 'like', "%{$kereses}%")->orWhere('leiras', 'like', "%{$kereses}%")));

        return response()->json($query->orderByDesc('prioritas')->orderBy('nev')->paginate());
    }

    public function show(Termek $termek): JsonResponse
    {
        abort_unless($termek->aktiv, 404);

        return response()->json($termek->load('allergenek'));
    }
}
