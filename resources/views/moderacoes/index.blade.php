@extends('layouts.app')

@section('title', 'Moderação — ' . $evento->titulo)

@section('content')
<div class="mb-4">
    <a
        href="{{ route('eventos.show', $evento) }}"
        class="btn btn-outline-light btn-sm mb-3"
    >
        Voltar ao evento
    </a>

    <h2 class="fw-bold">Fila de moderação</h2>

    <p class="text-secondary mb-1">
        {{ $evento->titulo }}
    </p>

    <p class="text-secondary">
        {{ $moderacoes->total() }} denúncia(s) registrada(s)
    </p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

@forelse($moderacoes as $moderacao)
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                <h5 class="mb-0">
                    Denúncia #{{ $moderacao->id }}
                </h5>

                <span class="badge {{ $moderacao->status === 'analisando' ? 'bg-warning text-dark' : ($moderacao->status === 'aprovado' ? 'bg-success' : 'bg-danger') }}">
                    {{ ucfirst($moderacao->status) }}
                </span>
            </div>

            <p class="fw-bold mb-1">Pergunta denunciada:</p>

            <p>{{ $moderacao->pergunta->texto }}</p>

            <p class="fw-bold mb-1">Motivo da denúncia:</p>

            <p>{{ $moderacao->motivo }}</p>

            <p class="text-secondary small">
                Recebida em
                {{ $moderacao->created_at->format('d/m/Y H:i') }}
            </p>

            <form
                action="{{ route('moderacoes.updateStatus', $moderacao) }}"
                method="POST"
                class="d-flex flex-wrap gap-2"
            >
                @csrf
                @method('PATCH')

                <button
                    type="submit"
                    name="status"
                    value="aprovado"
                    class="btn btn-success btn-sm"
                >
                    Aprovar pergunta
                </button>

                <button
                    type="submit"
                    name="status"
                    value="rejeitado"
                    class="btn btn-danger btn-sm"
                >
                    Rejeitar pergunta
                </button>
            </form>
        </div>
    </div>
@empty
    <div class="alert alert-dark">
        Nenhuma denúncia registrada para este evento.
    </div>
@endforelse

<div class="mt-4">
    {{ $moderacoes->links('pagination::bootstrap-5') }}
</div>
@endsection