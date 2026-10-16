<?php

use App\Http\Controllers\Admin\AllergenController as AdminAllergenController;
use App\Http\Controllers\Admin\FelhasznaloController as AdminFelhasznaloController;
use App\Http\Controllers\Admin\FutarController as AdminFutarController;
use App\Http\Controllers\Admin\RendelesController as AdminRendelesController;
use App\Http\Controllers\Admin\TermekController as AdminTermekController;
use App\Http\Controllers\AllergenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FogasController;
use App\Http\Controllers\FutarController;
use App\Http\Controllers\LegacyApiController;
use App\Http\Controllers\RendelesController;
use App\Http\Controllers\RendelesTetelController;
use App\Http\Controllers\TermekController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn (): array => ['status' => 'ok']);

Route::get('/felhasznalo', [LegacyApiController::class, 'felhasznaloLista']);
Route::post('/felhasznalo', [LegacyApiController::class, 'felhasznaloLetrehoz']);
Route::get('/felhasznalo/{felhasznalo_id}', [LegacyApiController::class, 'felhasznaloLekerdez']);
Route::get('/felhasznalok', [LegacyApiController::class, 'felhasznaloLista']);
Route::post('/felhasznalok', [LegacyApiController::class, 'felhasznaloLetrehoz']);
Route::get('/felhasznalok/{felhasznalo_id}', [LegacyApiController::class, 'felhasznaloLekerdez']);

Route::get('/termekek', [TermekController::class, 'index']);
Route::get('/termekek/{termek}', [TermekController::class, 'show']);
Route::get('/allergenek', [AllergenController::class, 'index']);
Route::get('/fogasok', [FogasController::class, 'index']);

Route::post('/bejelentkezes', [LegacyApiController::class, 'bejelentkezes']);
Route::get('/felhasznalo/email/{email}', [LegacyApiController::class, 'felhasznaloEmail']);
Route::put('/felhasznalo/{felhasznalo_id}', [LegacyApiController::class, 'felhasznalo']);
Route::delete('/felhasznalo/{felhasznalo_id}', [LegacyApiController::class, 'felhasznaloTorol']);
Route::get('/felhasznalo/{felhasznalo_id}/rendelesek', [LegacyApiController::class, 'felhasznaloRendelesek']);

Route::post('/futar', [LegacyApiController::class, 'futarLetrehoz']);
Route::get('/futar', [LegacyApiController::class, 'futarLista']);
Route::get('/futar/elerheto', [LegacyApiController::class, 'futarElerheto']);
Route::get('/futar/allapotok', [LegacyApiController::class, 'futarAllapotok']);
Route::get('/futar/szallitasra-alkalmas', [LegacyApiController::class, 'futarSzallitasraAlkalmas']);
Route::post('/futar/pozicio', [LegacyApiController::class, 'futarPozicio']);
Route::get('/futar/{futar_id}/poziciok', [LegacyApiController::class, 'futarPoziciok']);
Route::get('/futar/{futar_id}/rendelesek', [LegacyApiController::class, 'futarRendelesek']);
Route::patch('/futar/{futar_id}/allapot', [LegacyApiController::class, 'futarAllapot']);
Route::get('/futar/{futar_id}', [LegacyApiController::class, 'futar'])->whereNumber('futar_id');

Route::get('/allergen', [LegacyApiController::class, 'allergen']);
Route::post('/termek', [LegacyApiController::class, 'termekLetrehoz']);
Route::get('/termek', [LegacyApiController::class, 'termekLista']);
Route::get('/termek/{termek_id}', [LegacyApiController::class, 'termek']);
Route::post('/rendeles', [LegacyApiController::class, 'rendelesLetrehoz']);
Route::get('/rendeles', [LegacyApiController::class, 'rendelesLista']);
Route::get('/rendeles/{rendeles_id}/allergenek', [LegacyApiController::class, 'rendelesAllergenek']);
Route::patch('/rendeles/{rendeles_id}/statusz', [LegacyApiController::class, 'rendelesStatusz']);
Route::post('/rendeles/{rendeles_id}/lemondas', [LegacyApiController::class, 'rendelesLemondas']);
Route::get('/rendeles/{rendeles_id}', [LegacyApiController::class, 'rendeles']);

// Session-based auth: every route that logs in or needs a logged-in user or courier
Route::middleware('web')->group(function (): void {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/futar-login', [AuthController::class, 'futarLogin']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware(['auth', 'aktiv'])->group(function (): void {
        Route::get('/rendelesek', [RendelesController::class, 'index']);
        Route::post('/rendelesek', [RendelesController::class, 'store']);
        Route::get('/rendelesek/{rendeles}', [RendelesController::class, 'show']);
        Route::post('/rendelesek/{rendeles}/lemondas', [RendelesController::class, 'lemond']);
        Route::post('/rendelesek/{rendeles}/ertekeles', [RendelesController::class, 'ertekel']);
        Route::post('/rendelesek/{rendeles}/jatt', [RendelesController::class, 'jatt']);

        Route::get('/rendelesek/{rendeles}/tetelek', [RendelesTetelController::class, 'index']);
        Route::post('/rendelesek/{rendeles}/tetelek', [RendelesTetelController::class, 'store']);
        Route::patch('/rendelesek/{rendeles}/tetelek/{termek}', [RendelesTetelController::class, 'update']);
        Route::delete('/rendelesek/{rendeles}/tetelek/{termek}', [RendelesTetelController::class, 'destroy']);
    });

    Route::middleware(['auth', 'aktiv', 'role:admin'])->prefix('admin')->group(function (): void {
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
        Route::post('rendelesek/{rendeles}/keszul', [AdminRendelesController::class, 'keszul']);
    });

    Route::middleware('futar')->prefix('futar')->group(function (): void {
        Route::get('/en', [FutarController::class, 'en']);
        Route::get('/rendelesek', [FutarController::class, 'rendelesek']);
        Route::get('/sajat-rendelesek', [FutarController::class, 'sajatRendelesek']);
        Route::get('/rendelesek/{rendeles}/utvonal', [FutarController::class, 'utvonal']);
        Route::post('/rendelesek/{rendeles}/felvetel', [FutarController::class, 'felvesz']);
        Route::post('/rendelesek/{rendeles}/atvetel', [FutarController::class, 'atvesz']);
        Route::post('/rendelesek/{rendeles}/kiszallitas', [FutarController::class, 'kiszallit']);
        Route::patch('/allapot', [FutarController::class, 'allapot']);
    });
});
