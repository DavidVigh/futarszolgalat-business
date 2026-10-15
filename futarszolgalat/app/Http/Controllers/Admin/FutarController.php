<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFutarRequest;
use App\Http\Requests\Admin\UpdateFutarRequest;
use App\Models\Futar;
use Illuminate\Http\JsonResponse;

class FutarController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Futar::query()->with('aktualisPozicio')->latest('id')->paginate());
    }

    public function store(StoreFutarRequest $request): JsonResponse
    {
        return response()->json(Futar::create($request->validated())->refresh(), 201);
    }

    public function show(Futar $futar): JsonResponse
    {
        return response()->json($futar->load(['aktualisPozicio', 'aktivRendeles'])->loadCount('rendelesek'));
    }

    public function update(UpdateFutarRequest $request, Futar $futar): JsonResponse
    {
        $futar->update($request->validated());

        return response()->json($futar->refresh());
    }

    /** Orders keep their history without a courier (SET NULL); positions are deleted (BL 4). */
    public function destroy(Futar $futar): JsonResponse
    {
        abort_if($futar->aktivRendeles()->exists(), 422, 'Aktiv rendelessel rendelkezo futar nem torolheto.');

        $futar->delete();

        return response()->json(status: 204);
    }
}
