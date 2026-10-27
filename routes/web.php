<?php

use App\Http\Controllers\EventoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModeracaoController;

Route::middleware('auth')->group(function () {
    Route::get('/eventos/create', [EventoController::class, 'create'])
        ->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])
        ->name('eventos.store');
    Route::post('/eventos/{evento}/participar', [EventoController::class, 'toggleInscricao'])
        ->name('eventos.participar');
    Route::post('/perguntas/{pergunta}/moderar', [ModeracaoController::class,'store',])
        ->name('moderacoes.store'); 
        Route::get('/eventos/{evento}/moderacoes', [ModeracaoController::class,'index',])
        ->name('moderacoes.index');    
        Route::patch('/moderacoes/{moderacao}/status', [ModeracaoController::class,'updateStatus',])
        ->name('moderacoes.updateStatus');
});

Route::get('/', [EventoController::class, 'index'])->name('eventos.index');
Route::get('/eventos/{id}', [EventoController::class, 'show'])->name('eventos.show');
Route::post('/eventos/{id}/perguntas', [EventoController::class, 'storePergunta'])->name('eventos.perguntas.store');

Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/login', [LoginController::class, 'create'])->name('login.create');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');
