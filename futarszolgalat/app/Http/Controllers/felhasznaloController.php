<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class felhasznaloController extends Controller
{
    public function post(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validate([
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30', 'unique:felhasznalo,telszam'],
            'jelszo' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['adminE'] = false;

        $pythonApi->call('felhasznalo_hozzaadasa', $data);

        return response()->json(User::query()->where('telszam', $data['telszam'])->firstOrFail(), 201);
    }

    public function get(Request $request): JsonResponse
    {
        $request->validate(['id' => ['required', 'integer', 'exists:felhasznalo,id']]);

        return response()->json(User::findOrFail($request->integer('id')));
    }
}
