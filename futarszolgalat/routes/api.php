<?php

use App\Http\Controllers\Admin\FelhasznaloController as AdminFelhasznaloController;
use App\Http\Controllers\Admin\FutarController as AdminFutarController;
use App\Http\Controllers\Admin\RendelesController as AdminRendelesController;
use App\Http\Controllers\Admin\TermekController as AdminTermekController;
use App\Http\Controllers\AllergenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FutarController;
use App\Http\Controllers\TermekController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/futar-login', [AuthController::class, 'futarLogin']);
Route::get('/termekek', [TermekController::class, 'index']);
Route::get('/allergenek', [AllergenController::class, 'index']);

Route::middleware('web')->group(function (): void {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function (): void {
        Route::apiResource('felhasznalok', AdminFelhasznaloController::class)
            ->parameters(['felhasznalok' => 'user']);
        Route::apiResource('futarok', AdminFutarController::class)
            ->parameters(['futarok' => 'futar']);
        Route::apiResource('termekek', AdminTermekController::class)
            ->parameters(['termekek' => 'termek']);
        Route::apiResource('rendelesek', AdminRendelesController::class)
            ->parameters(['rendelesek' => 'rendeles']);
    });

    Route::middleware('futar')->prefix('futar')->group(function (): void {
        Route::get('/rendelesek', [FutarController::class, 'rendelesek']);
        Route::get('/sajat-rendelesek', [FutarController::class, 'sajatRendelesek']);
        Route::post('/rendelesek/{rendeles}/vallal', [FutarController::class, 'vallal']);
    });
});
