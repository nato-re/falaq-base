@extends('layouts.app')

@section('title', 'Moderação — ' . $evento->titulo . ' — FalaQ')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold m-0">🛡️ Moderação de Perguntas</h2>
        <p class="text-secondary m-0">{{ $evento->titulo }}</p>
    </div>
    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-outline-light btn-sm">&larr; Voltar ao evento</a>
</div>

@forelse($moderacoes as $moderacao)
    <div class="card mb-3 shadow-sm">
        <div class="card-body">
            <p class="fs-5 mb-1 text-white">{{ $moderacao->pergunta->texto }}</p>
            <p class="text-secondary mb-3"><strong>Motivo:</strong> {{ $moderacao->motivo }}</p>

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-secondary">
                    Status: <span class="badge {{ $moderacao->status === 'aprovada' ? 'bg-success' : ($moderacao->status === 'rejeitada' ? 'bg-danger' : 'bg-warning text-dark') }}">{{ $moderacao->status }}</span>
                    · {{ $moderacao->created_at->format('d/m/Y H:i') }}
                </span>

                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary" aria-label="Novo status">
                        @foreach(\App\Models\Moderacao::STATUS as $status)
                            <option value="{{ $status }}" @selected($moderacao->status === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Atualizar</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-dark text-center p-4">
        Nenhuma pergunta deste evento foi enviada para moderação.
    </div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $moderacoes->links('pagination::bootstrap-5') }}
</div>
@endsection
