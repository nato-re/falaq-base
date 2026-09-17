@extends('layouts.app')

@section('title', 'Criando Evento — FalaQ')

@section('content')
<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Evento</h4>
            <form action="{{ route('eventos.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="titulo" class="form-label text-secondary">Titulo</label>

                    <input name="titulo" id="titulo" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('titulo') is-invalid @enderror"></input>

                    @error('titulo')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label text-secondary">Descrição</label>

                    <input name="descricao" id="descricao" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('descricao') is-invalid @enderror"></input>

                    @error('descricao')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="data_evento" class="form-label text-secondary">data_evento</label>

                    <input name="data_evento" id="data_evento" rows="4" type="datetime-local"
                              class="form-control bg-dark text-white border-secondary @error('data_evento') is-invalid @enderror"></input>

                    @error('data_evento')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                {{-- <input hidden name="user_id" value="{{ Auth::user()->id }}" /> --}}

                <button type="submit" class="btn btn-primary w-100 fw-bold">Criar Evento</button>
            </form>
        </div>
    </div>
</div>

@endsection