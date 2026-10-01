<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Futar;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FutarController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Futar::latest('id')->paginate());
    }

    public function store(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $this->validated($request);
        $pythonApi->call('futar_hozzaadasa', $data);
        $futar = Futar::query()->where('telszam', $data['telszam'])->latest('id')->firstOrFail();

        return response()->json($futar, 201);
    }

    public function show(Futar $futar): JsonResponse
    {
        return response()->json($futar);
    }

    public function update(Request $request, Futar $futar, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('futar_modositasa', ['id' => $futar->id, ...$this->validated($request, true)]);

        return response()->json($futar->refresh());
    }

    public function destroy(Futar $futar, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('futar_torlese', ['id' => $futar->id]);

        return response()->json(status: 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'nev' => [$required, 'string', 'max:255'],
            'telszam' => [$required, 'string', 'max:30'],
            'jarmu' => [$required, Rule::in(['auto', 'dron', 'bicikli', 'motor'])],
            'ember' => [$required, 'boolean'],
            'allapot' => [$required, Rule::in(['elerheto', 'uton', 'szunet'])],
            'hatosugar_km' => [$required, 'integer', 'min:1'],
        ]);
    }
}
