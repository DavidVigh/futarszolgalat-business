<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminFelhasznaloStoreRequest;
use App\Http\Requests\AdminFelhasznaloUpdateRequest;
use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;

class FelhasznaloController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::query()->latest('id')->paginate());
    }

    public function store(AdminFelhasznaloStoreRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $pythonApi->call('felhasznalo_hozzaadasa', $data);
        $user = User::query()->where('telszam', $data['telszam'])->firstOrFail();

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(
        AdminFelhasznaloUpdateRequest $request,
        User $user,
        PythonApiClient $pythonApi
    ): JsonResponse {
        $data = $request->validated();
        $pythonApi->call('felhasznalo_modositasa', ['id' => $user->id, ...$data]);

        return response()->json($user->refresh());
    }

    public function destroy(User $user, PythonApiClient $pythonApi): JsonResponse
    {
        abort_if($user->id === auth()->id(), 422, 'Az aktiv admin nem torolheti sajat magat.');
        $pythonApi->call('felhasznalo_torlese', ['id' => $user->id]);

        return response()->json(status: 204);
    }
}
