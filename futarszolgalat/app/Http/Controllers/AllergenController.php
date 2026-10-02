<?php

namespace App\Http\Controllers;

use App\Models\Allergen;
use Illuminate\Http\JsonResponse;

class AllergenController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Allergen::query()->orderBy('nev')->get());
    }
}
