<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AllergenRequest;
use App\Models\Allergen;
use Illuminate\Http\JsonResponse;

class AllergenController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Allergen::query()->withCount('termekek')->orderBy('nev')->get());
    }

    public function store(AllergenRequest $request): JsonResponse
    {
        return response()->json(Allergen::create($request->validated()), 201);
    }

    public function show(Allergen $allergen): JsonResponse
    {
        return response()->json($allergen->loadCount('termekek'));
    }

    public function update(AllergenRequest $request, Allergen $allergen): JsonResponse
    {
        $allergen->update($request->validated());

        return response()->json($allergen->refresh());
    }

    /** An allergen in use cannot be deleted (BL 9). */
    public function destroy(Allergen $allergen): JsonResponse
    {
        abort_if($allergen->termekek()->exists(), 422, 'Hasznalatban levo allergen nem torolheto.');

        $allergen->delete();

        return response()->json(status: 204);
    }
}
