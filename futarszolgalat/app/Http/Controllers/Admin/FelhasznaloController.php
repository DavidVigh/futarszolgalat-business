<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFelhasznaloRequest;
use App\Http\Requests\Admin\UpdateFelhasznaloRequest;
use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class FelhasznaloController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::query()->latest('id')->paginate());
    }

    public function store(StoreFelhasznaloRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $pythonApi->call('felhasznalo_hozzaadasa', $data);
        $user = User::query()->where('email', $data['email'])->firstOrFail();

        return response()->json($user, 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->loadCount('rendelesek'));
    }

    public function update(UpdateFelhasznaloRequest $request, User $user, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
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
