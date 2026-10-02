<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRendelesRequest;
use App\Http\Requests\Admin\UpdateRendelesRequest;
use App\Models\Rendeles;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;

class RendelesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Rendeles::query()->with(['felhasznalo', 'futar'])->latest('id')->paginate());
    }

    public function store(StoreRendelesRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $pythonApi->call('rendeles_hozzaadasa', $data);
        $rendeles = Rendeles::query()->where('felhasznalo_id', $data['felhasznalo_id'])->latest('id')->firstOrFail();

        return response()->json($rendeles, 201);
    }

    public function show(Rendeles $rendeles): JsonResponse
    {
        return response()->json($rendeles->load(['felhasznalo', 'futar', 'termekek']));
    }

    public function update(UpdateRendelesRequest $request, Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('rendeles_modositasa', ['id' => $rendeles->id, ...$request->validated()]);

        return response()->json($rendeles->refresh());
    }

    public function destroy(Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('rendeles_torlese', ['id' => $rendeles->id]);

        return response()->json(status: 204);
    }
}
