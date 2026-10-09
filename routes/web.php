<?php

use App\Http\Controllers\EventoController;
use App\Http\Controllers\PerguntaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [EventoController::class, 'index'])->name('eventos.index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
    Route::post('/eventos/{id}/perguntas', [EventoController::class, 'storePergunta'])->name('eventos.perguntas.store');

    // TICKET #011: votar / remover voto em uma pergunta
    Route::post('/perguntas/{pergunta}/votar', [PerguntaController::class, 'votar'])->name('perguntas.votar');
});

require __DIR__.'/auth.php';