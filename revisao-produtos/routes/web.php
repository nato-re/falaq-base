<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

// Páginas de ilustração (prontas)
Route::view('/', 'home')->name('home');
Route::view('/sobre', 'sobre')->name('sobre');

// Autenticação
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Produtos — listagem PÚBLICA (sem middleware)
Route::get('/produtos', [ProdutoController::class, 'index'])->name('produtos.index');

// R3 + R4 — cadastro: precisa estar logado (auth) E ser vendedor (vendedor)
// A ordem importa: auth primeiro, senão Auth::user() seria null no VendedorMiddleware.
Route::middleware(['auth', 'vendedor'])->group(function () {
    Route::get('/produtos/create', [ProdutoController::class, 'create'])->name('produtos.create');
    Route::post('/produtos', [ProdutoController::class, 'store'])->name('produtos.store');
});

// R3 — compras: só precisa estar logado
Route::middleware('auth')->group(function () {
    Route::get('/compras', [CompraController::class, 'index'])->name('compras.index');
    Route::get('/compras/create', [CompraController::class, 'create'])->name('compras.create');
    Route::post('/compras', [CompraController::class, 'store'])->name('compras.store');
});
