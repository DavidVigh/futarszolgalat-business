<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rendeles;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RendelesController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Rendeles::latest('id')->paginate());
    }

    public function store(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $this->validated($request);
        $pythonApi->call('rendeles_hozzaadasa', $data);
        $rendeles = Rendeles::query()->where('felhasznalo_id', $data['felhasznalo_id'])->latest('id')->firstOrFail();

        return response()->json($rendeles, 201);
    }

    public function show(Rendeles $rendeles): JsonResponse
    {
        return response()->json($rendeles);
    }

    public function update(Request $request, Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('rendeles_modositasa', ['id' => $rendeles->id, ...$this->validated($request, true)]);

        return response()->json($rendeles->refresh());
    }

    public function destroy(Rendeles $rendeles, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('rendeles_torlese', ['id' => $rendeles->id]);

        return response()->json(status: 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'felhasznalo_id' => [$required, 'integer', 'exists:felhasznalo,id'],
            'futar_id' => ['sometimes', 'nullable', 'integer', 'exists:futar,id'],
            'cim' => [$required, 'string', 'max:255'],
            'prioritas' => ['sometimes', 'boolean'],
            'fizetesi_mod' => [$required, Rule::in(['keszpenz', 'kartya'])],
            'jatt' => ['sometimes', 'nullable', 'integer', 'gt:0'],
            'szamla' => [$required, 'boolean'],
            'km' => [$required, 'integer', 'gt:0'],
            'statusz' => ['sometimes', 'string', 'max:50'],
            'ertekeles' => [$required, 'integer', 'between:1,5'],
        ]);
    }
}
