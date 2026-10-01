<?php

namespace App\Http\Controllers;

use App\Models\Futar;
use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validate([
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30', 'unique:felhasznalo,telszam'],
            'jelszo' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $data['adminE'] = false;

        $pythonApi->call('felhasznalo_hozzaadasa', $data);
        $user = User::query()->where('telszam', $data['telszam'])->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'telszam' => ['required', 'string'],
            'jelszo' => ['required', 'string'],
        ]);
        $credentials = ['telszam' => $data['telszam'], 'password' => $data['jelszo']];

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Hibas bejelentkezesi adatok.'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $request->user()]);
    }

    public function futarLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nev' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30'],
        ]);
        $futar = Futar::query()
            ->where('nev', $data['nev'])
            ->where('telszam', $data['telszam'])
            ->first();

        if ($futar === null) {
            return response()->json(['message' => 'A futar nem talalhato.'], 422);
        }

        $request->session()->put('futar_id', $futar->id);
        $request->session()->regenerate();

        return response()->json(['futar' => $futar]);
    }

    public function logout(Request $request): JsonResponse
    {
        if (Auth::check()) {
            Auth::logout();
        }
        $request->session()->forget('futar_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sikeres kijelentkezes.']);
    }
}
