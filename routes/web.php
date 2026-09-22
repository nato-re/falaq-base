<?php

use App\Http\Controllers\EventoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
Route::post('/eventos/{evento}/perguntas', [EventoController::class, 'storePergunta'])
    ->name('eventos.perguntas.store')
    ->middleware('auth');
