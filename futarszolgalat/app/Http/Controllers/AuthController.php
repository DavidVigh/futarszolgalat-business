<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\FutarLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Futar;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([...$request->validated(), 'isAdmin' => false]);
        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $user], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json(['message' => 'Hibas bejelentkezesi adatok.'], 422);
        }

        $request->session()->regenerate();

        return response()->json(['user' => $request->user()]);
    }

    public function futarLogin(FutarLoginRequest $request): JsonResponse
    {
        $futar = Futar::query()
            ->where('nev', $request->validated('nev'))
            ->where('telszam', $request->validated('telszam'))
            ->first();

        if ($futar === null) {
            return response()->json(['message' => 'A futar nem talalhato.'], 422);
        }

        $request->session()->regenerate();
        $request->session()->put('futar_id', $futar->id);

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
