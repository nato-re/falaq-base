@extends('layouts.app')

@section('title', 'Produtos — Loja de Produtos')

@section('content')
<h2 class="fw-bold mb-3">📦 Produtos à venda</h2>

<div class="card shadow-sm">
    <table class="table table-dark table-hover mb-0">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Garantia (meses)</th>
                <th>Preço (R$)</th>
                <th>Estoque</th>
                <th>Vendedor</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($produtos ?? [] as $produto)
                <tr>
                    <td>{{ $produto->nome }}</td>
                    <td>{{ $produto->categoria }}</td>
                    <td>{{ $produto->garantia_meses }}</td>
                    <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                    <td>{{ $produto->estoque }}</td>
                    <td>{{ $produto->vendedor->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="99" class="text-center text-secondary py-4">Nenhum produto para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
