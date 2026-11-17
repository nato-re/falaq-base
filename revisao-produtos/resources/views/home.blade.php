@extends('layouts.app')

@section('content')
<div class="card shadow-sm p-5 mb-4">
    <h1 class="fw-bold">Bem-vindo à Loja de Produtos 🛍️</h1>
    <p class="lead text-secondary mb-4">
        Um marketplace simples: vendedores cadastram Produtos e clientes compram.
    </p>
    <div>
        <a href="{{ route('produtos.index') }}" class="btn btn-primary btn-lg me-2">Ver Produtos</a>
        <a href="{{ route('compras.create') }}" class="btn btn-outline-light btn-lg">Fazer uma compra</a>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card h-100 p-3">
            <h5 class="fw-bold">🏪 Para vendedores</h5>
            <p class="text-secondary mb-0">Entre com sua conta de vendedor e cadastre novos produtos no catálogo.</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 p-3">
            <h5 class="fw-bold">🛒 Para clientes</h5>
            <p class="text-secondary mb-0">Escolha um ou mais produtos, informe as quantidades e finalize a compra.</p>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100 p-3">
            <h5 class="fw-bold">🧾 Histórico</h5>
            <p class="text-secondary mb-0">Acompanhe em "Minhas compras" tudo o que você já comprou, com o total de cada pedido.</p>
        </div>
    </div>
</div>
@endsection
