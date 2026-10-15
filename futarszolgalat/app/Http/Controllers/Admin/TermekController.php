<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTermekRequest;
use App\Http\Requests\Admin\UpdateTermekRequest;
use App\Models\Termek;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class TermekController extends Controller
{
    /** Every product, inactive ones included. */
    public function index(): JsonResponse
    {
        return response()->json(Termek::query()->with('allergenek')->latest('id')->paginate());
    }

    public function store(StoreTermekRequest $request): JsonResponse
    {
        $termek = DB::transaction(function () use ($request): Termek {
            $termek = Termek::create(Arr::except($request->validated(), 'allergenek'));
            $termek->allergenek()->sync($request->validated('allergenek', []));

            return $termek;
        });

        return response()->json($termek->refresh()->load('allergenek'), 201);
    }

    public function show(Termek $termek): JsonResponse
    {
        return response()->json($termek->load('allergenek'));
    }

    public function update(UpdateTermekRequest $request, Termek $termek): JsonResponse
    {
        DB::transaction(function () use ($request, $termek): void {
            $termek->update(Arr::except($request->validated(), 'allergenek'));
            if ($request->has('allergenek')) {
                $termek->allergenek()->sync($request->validated('allergenek', []));
            }
        });

        return response()->json($termek->refresh()->load('allergenek'));
    }

    /** A product that was ever ordered can only be deactivated (BL 7). */
    public function destroy(Termek $termek): JsonResponse
    {
        abort_if($termek->rendelesek()->exists(), 422, 'Rendelesben mar szerepelt termek nem torolheto, csak inaktivalhato (aktiv: false).');

        $termek->delete();

        return response()->json(status: 204);
    }
}
