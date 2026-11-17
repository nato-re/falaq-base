@extends('layouts.app')

@section('title', 'Comprar — Loja de Produtos')

@section('content')
<h2 class="fw-bold mb-3">🛒 Nova compra</h2>
<p class="text-secondary">Informe a quantidade de cada item. Itens com quantidade 0 ficam de fora da compra.</p>

<form method="POST" action="{{ route('compras.store') }}">
    @csrf
    <div class="card shadow-sm mb-3">
        <table class="table table-dark align-middle mb-0">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço</th>
                    <th>Em estoque</th>
                    <th style="width: 9rem">Quantidade</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($produtos ?? [] as $produto)
                    <tr>
                        <td>{{ $produto->nome }}</td>
                        <td>R$ {{ number_format($produto->preco, 2, ',', '.') }}</td>
                        <td>{{ $produto->estoque }}</td>
                        <td>
                            <input type="number" min="0" name="itens[{{ $produto->id }}]"
                                   value="{{ old('itens.' . $produto->id, 0) }}" class="form-control">
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">Nenhum produto para mostrar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary fw-bold">Finalizar compra</button>
</form>
@endsection
