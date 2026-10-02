<?php

namespace App\Http\Controllers;

use App\Http\Requests\FelhasznaloIdRequest;
use App\Http\Requests\FelhasznaloStoreRequest;
use App\Models\User;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;

class felhasznaloController extends Controller
{
    public function post(FelhasznaloStoreRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $data['adminE'] = false;

        $pythonApi->call('felhasznalo_hozzaadasa', $data);

        return response()->json(User::query()->where('telszam', $data['telszam'])->firstOrFail(), 201);
    }

    public function get(FelhasznaloIdRequest $request): JsonResponse
    {
        return response()->json(User::findOrFail($request->integer('id')));
    }
}
