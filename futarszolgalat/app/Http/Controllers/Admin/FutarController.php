<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFutarRequest;
use App\Http\Requests\Admin\UpdateFutarRequest;
use App\Models\Futar;
use App\Services\FutarPozicioClient;
use Illuminate\Http\JsonResponse;

class FutarController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Futar::query()->latest('id')->paginate());
    }

    public function store(StoreFutarRequest $request): JsonResponse
    {
        return response()->json(Futar::create($request->validated())->refresh(), 201);
    }

    public function show(Futar $futar, FutarPozicioClient $poziciok): JsonResponse
    {
        $futar->load('aktivRendeles')->loadCount('rendelesek');

        return response()->json([...$futar->toArray(), 'pozicio' => $poziciok->aktualis($futar->id)]);
    }

    public function update(UpdateFutarRequest $request, Futar $futar): JsonResponse
    {
        abort_if(
            $request->has('aktiv') && ! $request->boolean('aktiv') && $futar->aktivRendeles()->exists(),
            422,
            'Aktiv rendelessel rendelkezo futar nem inaktivalhato.',
        );

        $futar->update($request->validated());

        return response()->json($futar->refresh());
    }

    /** Couriers are deactivated, not deleted: their orders keep them (ON DELETE RESTRICT). */
    public function destroy(Futar $futar): JsonResponse
    {
        abort_if($futar->aktivRendeles()->exists(), 422, 'Aktiv rendelessel rendelkezo futar nem inaktivalhato.');

        $futar->update(['aktiv' => false]);

        return response()->json($futar->refresh());
    }
}
