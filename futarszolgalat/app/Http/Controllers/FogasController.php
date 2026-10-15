<?php

namespace App\Http\Controllers;

use App\Models\Fogas;
use Illuminate\Http\JsonResponse;

class FogasController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Fogas::query()->orderBy('tipus')->orderBy('kod')->get());
    }
}
