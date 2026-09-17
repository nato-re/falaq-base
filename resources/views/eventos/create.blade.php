@extends('layouts.app')

@section('title', 'Criar Evento — FalaQ')

@section('content')
<div class="row">
    <!-- Formularço de envio de Pergunta -->
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm p-3">
            <h4 class="fw-bold mb-3">💬 Faça sua Pergunta</h4>
            <form action="{{ route('eventos.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="titulo" class="form-label text-secondary">Titulo do Evento</label>

                    <input name="titulo" id="titulo" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('titulo') is-invalid @enderror"></input>

                    @error('titulo')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label text-secondary">Descrição do Evento</label>

                    <input name="descricao" id="descricao" rows="4" 
                              class="form-control bg-dark text-white border-secondary @error('descricao') is-invalid @enderror"></input>

                    @error('descricao')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="data_evento" class="form-label text-secondary">Data</label>

                    <input name="data_evento" id="data_evento" rows="4"  type="date"
                              class="form-control bg-dark text-white border-secondary @error('data_evento') is-invalid @enderror"></input>

                    @error('data_evento')
                        <div class="invalid-feedback fw-bold">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Criar Evento</button>
            </form>
        </div>
    </div>
@endsection