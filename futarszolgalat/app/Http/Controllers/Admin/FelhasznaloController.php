<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FelhasznaloController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::query()->latest('id')->paginate());
    }

    public function store(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $this->validated($request);
        $pythonApi->call('felhasznalo_hozzaadasa', $data);
        $user = User::query()->where('telszam', $data['telszam'])->firstOrFail();

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validate([
            'nev' => ['sometimes', 'required', 'string', 'max:255'],
            'cim' => ['sometimes', 'required', 'string', 'max:255'],
            'telszam' => ['sometimes', 'required', 'string', 'max:30', Rule::unique('felhasznalo', 'telszam')->ignore($user->id)],
            'jelszo' => ['sometimes', 'required', 'string', 'min:8', 'confirmed'],
            'adminE' => ['sometimes', 'boolean'],
        ]);
        $pythonApi->call('felhasznalo_modositasa', ['id' => $user->id, ...$data]);

        return response()->json($user->refresh());
    }

    public function destroy(User $user, PythonApiClient $pythonApi): JsonResponse
    {
        abort_if($user->id === auth()->id(), 422, 'Az aktiv admin nem torolheti sajat magat.');
        $pythonApi->call('felhasznalo_torlese', ['id' => $user->id]);

        return response()->json(status: 204);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nev' => ['required', 'string', 'max:255'],
            'cim' => ['required', 'string', 'max:255'],
            'telszam' => ['required', 'string', 'max:30', 'unique:felhasznalo,telszam'],
            'jelszo' => ['required', 'string', 'min:8', 'confirmed'],
            'adminE' => ['required', 'boolean'],
        ]);
    }
}
