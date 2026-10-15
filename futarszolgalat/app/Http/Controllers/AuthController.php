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
use Illuminate\Support\Facades\Hash;

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
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null || ! $user->aktiv || ! $this->jelszoEgyezik($user, $request->validated('password'))) {
            return response()->json(['message' => 'Hibas bejelentkezesi adatok.'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json(['user' => $request->user()]);
    }

    public function futarLogin(FutarLoginRequest $request): JsonResponse
    {
        $futar = Futar::query()
            ->where('aktiv', true)
            ->where('nev', $request->validated('nev'))
            ->where('telszam', $request->validated('telszam'))
            ->first();

        if ($futar === null) {
            return response()->json(['message' => 'A futar nem talalhato, vagy inaktiv.'], 422);
        }

        $request->session()->regenerate();
        $request->session()->put('futar_id', $futar->id);

        return response()->json(['futar' => $futar]);
    }

    /**
     * Checks the password and upgrades a plaintext one (left over from older DB exports)
     * to a bcrypt hash on the first successful login.
     */
    private function jelszoEgyezik(User $user, string $jelszo): bool
    {
        $tarolt = (string) $user->getRawOriginal('password');

        if (Hash::info($tarolt)['algoName'] !== 'unknown') {
            return Hash::check($jelszo, $tarolt);
        }

        if (! hash_equals($tarolt, $jelszo)) {
            return false;
        }

        $user->forceFill(['password' => Hash::make($jelszo)])->save();

        return true;
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
