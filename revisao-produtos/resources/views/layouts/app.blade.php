<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Loja de Produtos')</title>
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #0f1117; color: #e2e8f0; font-family: system-ui, sans-serif; }
        .card { background-color: #1a1d27; border: 1px solid #2e3347; color: #e2e8f0; }
        .navbar { background-color: #1a1d27; border-bottom: 1px solid #2e3347; }
        .btn-primary { background-color: #7c6af7; border-color: #7c6af7; }
        .btn-primary:hover { background-color: #6352de; border-color: #6352de; }
        .table-dark { --bs-table-bg: #1a1d27; --bs-table-border-color: #2e3347; }
        .form-control, .form-select { background-color: #0f1117; color: #e2e8f0; border-color: #2e3347; }
        .form-control:focus, .form-select:focus { background-color: #0f1117; color: #e2e8f0; }
        .badge-tipo { background-color: #7c6af7; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">🛒 Loja de Produtos</a>

            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="{{ route('produtos.index') }}">Produtos</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('produtos.create') }}">Cadastrar Produto</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('compras.create') }}">Comprar</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('compras.index') }}">Minhas compras</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('sobre') }}">Sobre</a></li>
            </ul>

            @auth
                <span class="navbar-text me-3">
                    {{ Auth::user()->name }}
                    <span class="badge badge-tipo">{{ Auth::user()->tipo }}</span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm">Sair</button>
                </form>
            @else
                <a class="btn btn-primary btn-sm" href="{{ route('login') }}">Entrar</a>
            @endauth
        </div>
    </nav>

    <main class="container pb-5">
        @if (session('sucesso'))
            <div class="alert alert-success border-0 shadow-sm">{{ session('sucesso') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
