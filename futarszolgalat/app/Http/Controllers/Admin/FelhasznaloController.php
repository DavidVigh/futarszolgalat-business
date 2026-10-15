<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFelhasznaloRequest;
use App\Http\Requests\Admin\UpdateFelhasznaloRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class FelhasznaloController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(User::query()->withCount('rendelesek')->latest('id')->paginate());
    }

    public function store(StoreFelhasznaloRequest $request): JsonResponse
    {
        return response()->json(User::create($request->validated()), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->loadCount('rendelesek'));
    }

    public function update(UpdateFelhasznaloRequest $request, User $user): JsonResponse
    {
        $user->update($request->validated());

        return response()->json($user->refresh());
    }

    public function destroy(User $user): JsonResponse
    {
        abort_if($user->id === auth()->id(), 422, 'Az aktiv admin nem torolheti sajat magat.');
        // The DB keeps order history: rendeles.felhasznalo_id is ON DELETE RESTRICT
        abort_if($user->rendelesek()->exists(), 422, 'Rendelessel rendelkezo felhasznalo nem torolheto.');

        $user->delete();

        return response()->json(status: 204);
    }
}
