<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;

// Rota principal (Página inicial)
Route::get('/', [EventoController::class, 'index'])->name('eventos.index');

// Dashboard do utilizador logado
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Grupo de rotas protegidas (apenas utilizadores logados)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
    Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
});

// Ver os detalhes do evento (Público, usa {evento} para o Route Model Binding funcionar)
Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');

// TICKET #005: Rota de submissão de perguntas protegida com middleware auth
Route::post('/eventos/{id}/perguntas', [EventoController::class, 'storePergunta'])
    ->name('eventos.perguntas.store')
    ->middleware('auth');

// Rotas de Autenticação (Login e Registo)
Route::get('/register', [RegisterController::class, 'create'])->name('register.create');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
Route::get('/login', [LoginController::class, 'create'])->name('login.create');
Route::post('/login', [LoginController::class, 'store'])->name('login.store');
Route::post('/logout', [LoginController::class, 'logout'])->name('auth.logout');