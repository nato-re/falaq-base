<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FalaQ-Eu_T_3scuto')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f1117; color: #e2e8f0; font-family: system-ui, sans-serif; }
        .card { background-color: #1a1d27; border: 1px solid #2e3347; color: #e2e8f0; }
        .navbar { background-color: #1a1d27; border-bottom: 1px solid #2e3347; }
        .btn-primary { background-color: #7c6af7; border-color: #7c6af7; }
        .btn-primary:hover { background-color: #6352de; border-color: #6352de; }
        .pagination .page-link { background-color: #1a1d27; border-color: #2e3347; color: #7c6af7; }
        .pagination .page-item.active .page-link { background-color: #7c6af7; border-color: #7c6af7; color: #fff; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('eventos.index') }}">
                🚀 FalaQ-Eu_T_3scuto <span class="badge bg-secondary fs-6">MVP</span>
            </a>
            @yield('navbar_item')
            @auth
            <p>
                Olá, {{ Auth::user()->name }}
            </p>
            @endauth
            @guest          
                <a class="navbar-brand" href="{{ route('login') }}">
                    Login
                </a>
            <a class="navbar-brand" href="{{ route('register') }}">
                Registro
            </a>
            @endguest

        </div>
    </nav>

    <div class="container pb-5">
        @if(session('sucesso'))
            <div class="alert alert-success border-0 shadow-sm mb-4">
                {{ session('sucesso') }}
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>