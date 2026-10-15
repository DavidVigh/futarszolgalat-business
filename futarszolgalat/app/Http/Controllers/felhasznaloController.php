<?php

namespace App\Http\Controllers;

use App\Http\Requests\FelhasznaloStoreRequest;
use App\Services\PythonApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class felhasznaloController extends Controller
{
    public function index(Request $request, PythonApiClient $pythonApi): JsonResponse
    {
        return response()->json($pythonApi->get('felhasznalok_lekerese', $request->query()));
    }

    public function store(FelhasznaloStoreRequest $request, PythonApiClient $pythonApi): JsonResponse
    {
        $data = $request->validated();
        $data['admin'] = $data['admin'] ?? false;

        return response()->json(
            $pythonApi->call('felhasznalo_hozzaadasa', $data),
            201
        );
    }

    public function show(int $felhasznalo_id, PythonApiClient $pythonApi): JsonResponse
    {
        return response()->json(
            $pythonApi->get('felhasznalo_lekerese', ['felhasznalo_id' => $felhasznalo_id])
        );
    }
}
