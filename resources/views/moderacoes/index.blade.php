@extends('layouts.app')

@section('title', 'Moderações — ' . $evento->titulo)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0">🛡️ Moderações — {{ $evento->titulo }}</h3>
    <a href="{{ route('eventos.show', $evento->id) }}" class="btn btn-outline-secondary btn-sm">Voltar ao evento</a>
</div>

@forelse($moderacoes as $moderacao)
    <div class="card mb-3 shadow-sm border-start border-4 border-warning">
        <div class="card-body">
            <p class="text-secondary small mb-1">Pergunta:</p>
            <p class="fs-5 mb-2 text-white">{{ $moderacao->pergunta->texto }}</p>
            <p class="mb-3"><span class="text-secondary">Motivo:</span> {{ $moderacao->motivo }}</p>

            <div class="d-flex justify-content-between align-items-center small">
                <span class="text-secondary">
                    Status: <span class="badge bg-secondary">{{ $moderacao->status }}</span>
                    · {{ $moderacao->created_at->format('d/m/Y H:i') }}
                </span>

                <form action="{{ route('moderacoes.status', $moderacao) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="form-select form-select-sm bg-dark text-white border-secondary">
                        @foreach(\App\Http\Controllers\ModeracaoController::STATUS as $status)
                            <option value="{{ $status }}" @selected($moderacao->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm">Atualizar</button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="alert alert-dark text-center p-4">
        Nenhuma solicitação de moderação para este evento.
    </div>
@endforelse

<div class="d-flex justify-content-center mt-4">
    {{ $moderacoes->links('pagination::bootstrap-5') }}
</div>
@endsection
