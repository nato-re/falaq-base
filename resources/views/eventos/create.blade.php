@extends('layouts.app')

@section('title', 'Criar Evento — FalaQ')

@section('content')

<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-2xl bg-gray-800 rounded-lg shadow-lg p-6">

        <h2 class="text-2xl font-bold text-white mb-6 text-center">
            Criar Evento
        </h2>

        <form action="{{ route('eventos.store') }}" method="POST">
            @csrf

            {{-- Título --}}
            <div class="mb-5">
                <label for="titulo" class="block text-white font-semibold mb-2">
                    Título do Evento
                </label>

                <input
                    type="text"
                    name="titulo"
                    id="titulo"
                    value="{{ old('titulo') }}"
                    class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border
                           border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('titulo') border-red-500 @enderror"
                    placeholder="Digite o título do evento"
                >

                @error('titulo')
                    <div class="text-red-500 mt-2">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Descrição --}}
            <div class="mb-5">
                <label for="descricao" class="block text-white font-semibold mb-2">
                    Descrição do Evento
                </label>

                <textarea
                    name="descricao"
                    id="descricao"
                    rows="5"
                    class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border
                           border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('descricao') border-red-500 @enderror"
                    placeholder="Digite a descrição do evento"
                >{{ old('descricao') }}</textarea>

                @error('descricao')
                    <div class="text-red-500 mt-2">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Data --}}
            <div class="mb-5">
                <label for="data_evento" class="block text-white font-semibold mb-2">
                    Data do Evento
                </label>

                <input
                    type="date"
                    name="data_evento"
                    id="data_evento"
                    value="{{ old('data_evento') }}"
                    class="w-full px-4 py-2 bg-gray-700 text-white rounded-md border border-gray-600
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           @error('data_evento') border-red-500 @enderror"
                >

                @error('data_evento')
                    <div class="text-red-500 mt-2">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Botão --}}
            <button
                type="submit"
                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md
                       hover:bg-blue-700 font-bold transition"
            >
                Criar Evento
            </button>

        </form>
    </div>
</div>

@endsection