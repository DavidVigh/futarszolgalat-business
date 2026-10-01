<?php

namespace App\Http\Controllers;

use App\Models\Termek;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TermekController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Termek::query();
        $query->when($request->filled('tipus'), fn ($q) => $q->whereIn('tipus', (array) $request->input('tipus')));
        $query->when($request->filled('fogas'), fn ($q) => $q->whereIn('fogas', (array) $request->input('fogas')));
        $query->when($request->filled('kereses'), fn ($q) => $q->where(fn ($q) => $q->where('nev', 'like', '%'.$request->input('kereses').'%')->orWhere('leiras', 'like', '%'.$request->input('kereses').'%')));

        return response()->json($query->orderBy('nev')->paginate());
    }
}
