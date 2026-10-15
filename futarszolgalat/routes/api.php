<?php

use App\Http\Controllers\Admin\AllergenController as AdminAllergenController;
use App\Http\Controllers\Admin\FelhasznaloController as AdminFelhasznaloController;
use App\Http\Controllers\Admin\FutarController as AdminFutarController;
use App\Http\Controllers\Admin\RendelesController as AdminRendelesController;
use App\Http\Controllers\Admin\TermekController as AdminTermekController;
use App\Http\Controllers\AllergenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FutarController;
use App\Http\Controllers\RendelesController;
use App\Http\Controllers\RendelesTetelController;
use App\Http\Controllers\TermekController;
use Illuminate\Support\Facades\Route;

Route::get('/termekek', [TermekController::class, 'index']);
Route::get('/termekek/{termek}', [TermekController::class, 'show']);
Route::get('/allergenek', [AllergenController::class, 'index']);

// Session-based auth: every route that logs in or needs a logged-in user or courier
Route::middleware('web')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/futar-login', [AuthController::class, 'futarLogin']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('auth')->group(function (): void {
        Route::get('/rendelesek', [RendelesController::class, 'index']);
        Route::post('/rendelesek', [RendelesController::class, 'store']);
        Route::get('/rendelesek/{rendeles}', [RendelesController::class, 'show']);
        Route::post('/rendelesek/{rendeles}/lemondas', [RendelesController::class, 'lemond']);
        Route::post('/rendelesek/{rendeles}/ertekeles', [RendelesController::class, 'ertekel']);

        Route::post('/rendelesek/{rendeles}/tetelek', [RendelesTetelController::class, 'store']);
        Route::patch('/rendelesek/{rendeles}/tetelek/{termek}', [RendelesTetelController::class, 'update']);
        Route::delete('/rendelesek/{rendeles}/tetelek/{termek}', [RendelesTetelController::class, 'destroy']);
    });

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
        Route::apiResource('felhasznalok', AdminFelhasznaloController::class)
            ->parameters(['felhasznalok' => 'user']);
        Route::apiResource('futarok', AdminFutarController::class)
            ->parameters(['futarok' => 'futar']);
        Route::apiResource('termekek', AdminTermekController::class)
            ->parameters(['termekek' => 'termek']);
        Route::apiResource('allergenek', AdminAllergenController::class)
            ->parameters(['allergenek' => 'allergen']);
        Route::apiResource('rendelesek', AdminRendelesController::class)
            ->only(['index', 'show'])
            ->parameters(['rendelesek' => 'rendeles']);
    });

    Route::middleware('futar')->prefix('futar')->group(function (): void {
        Route::get('/en', [FutarController::class, 'en']);
        Route::get('/rendelesek', [FutarController::class, 'rendelesek']);
        Route::get('/sajat-rendelesek', [FutarController::class, 'sajatRendelesek']);
        Route::post('/rendelesek/{rendeles}/felvetel', [FutarController::class, 'felvesz']);
        Route::post('/rendelesek/{rendeles}/atvetel', [FutarController::class, 'atvesz']);
        Route::post('/rendelesek/{rendeles}/kiszallitas', [FutarController::class, 'kiszallit']);
        Route::patch('/allapot', [FutarController::class, 'allapot']);
        Route::post('/pozicio', [FutarController::class, 'pozicio']);
    });
});
