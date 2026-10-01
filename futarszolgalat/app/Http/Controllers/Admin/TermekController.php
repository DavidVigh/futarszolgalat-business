<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Termek;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TermekController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Termek::latest('id')->paginate());
    }

    public function store(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $this->validated($request);
        $pythonApi->call('termek_hozzaadasa', $data);
        $termek = Termek::query()->where('nev', $data['nev'])->latest('id')->firstOrFail();

        return response()->json($termek, 201);
    }

    public function show(Termek $termek): JsonResponse
    {
        return response()->json($termek);
    }

    public function update(Request $request, Termek $termek, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('termek_modositasa', ['id' => $termek->id, ...$this->validated($request, true)]);

        return response()->json($termek->refresh());
    }

    public function destroy(Termek $termek, PythonApiClient $pythonApi): JsonResponse
    {
        $pythonApi->call('termek_torlese', ['id' => $termek->id]);

        return response()->json(status: 204);
    }

    private function validated(Request $request, bool $partial = false): array
    {
        $required = $partial ? 'sometimes' : 'required';

        return $request->validate([
            'tipus' => [$required, Rule::in(['etel', 'ital'])],
            'nev' => [$required, 'string', 'max:255'],
            'fogas' => [$required, 'string', 'max:255'],
            'leiras' => ['sometimes', 'nullable', 'string'],
            'ar' => [$required, 'integer', 'min:0'],
            'elkeszitesi_ido' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'prioritas' => ['sometimes', 'boolean'],
        ]);
    }
}
