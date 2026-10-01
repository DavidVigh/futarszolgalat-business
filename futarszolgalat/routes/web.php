<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/termekek', [App\Http\Controllers\TermekController::class, 'get'])->name('termekek.index');

Route::get('/termekek/{id}', [App\Http\Controllers\TermekController::class, 'get'])->name('termekek.show');

Route::post('/termekek', [App\Http\Controllers\TermekController::class, 'post'])->name('termekek.store');

Route::put('/termekek/{id}', [App\Http\Controllers\TermekController::class, 'put'])->name('termekek.update');

Route::delete('/termekek/{id}', [App\Http\Controllers\TermekController::class, 'delete'])->name('termekek.destroy');

