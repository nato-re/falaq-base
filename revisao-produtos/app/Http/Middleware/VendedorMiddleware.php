<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// R4 — criado com: php artisan make:middleware VendedorMiddleware
class VendedorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se não for vendedor, interrompe com 403 (mostra errors/403.blade.php)
        if (Auth::user()->tipo !== 'vendedor') {
            abort(403);
        }

        // Se for vendedor, deixa a requisição seguir para o controller
        return $next($request);
    }
}
