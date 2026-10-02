<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFutarRequest;
use App\Http\Requests\Admin\UpdateFutarRequest;
use App\Models\Futar;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;

class FutarController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Futar::query()->with('utolsoPozicio')->latest('id')->paginate());
    }

    public function store(StoreFutarRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $pythonApi->call('futar_hozzaadasa', $data);
        $futar = Futar::query()->where('telszam', $data['telszam'])->latest('id')->firstOrFail();

        return response()->json($futar, 201);
    }

    public function show(Futar $futar): JsonResponse
    {
        return response()->json($futar->load('utolsoPozicio')->loadCount('rendelesek'));
    }

    public function update(UpdateFutarRequest $request, Futar $futar, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('futar_modositasa', ['id' => $futar->id, ...$request->validated()]);

        return response()->json($futar->refresh());
    }

    public function destroy(Futar $futar, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('futar_torlese', ['id' => $futar->id]);

        return response()->json(status: 204);
    }
}
