<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTermekRequest;
use App\Http\Requests\Admin\UpdateTermekRequest;
use App\Models\Termek;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;

class TermekController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Termek::query()->with('allergenek')->latest('id')->paginate());
    }

    public function store(StoreTermekRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $pythonApi->call('termek_hozzaadasa', $data);
        $termek = Termek::query()->where('nev', $data['nev'])->latest('id')->firstOrFail();

        return response()->json($termek, 201);
    }

    public function show(Termek $termek): JsonResponse
    {
        return response()->json($termek->load('allergenek'));
    }

    public function update(UpdateTermekRequest $request, Termek $termek, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('termek_modositasa', ['id' => $termek->id, ...$request->validated()]);

        return response()->json($termek->refresh());
    }

    public function destroy(Termek $termek, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('termek_torlese', ['id' => $termek->id]);

        return response()->json(status: 204);
    }
}
