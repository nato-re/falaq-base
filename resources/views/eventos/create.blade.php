@extends('layouts.app')

@section('title', 'Criar Evento — FalaQ')

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-gray-900">Criar Evento</h2>

    <form action="{{ route('eventos.store') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="titulo" class="block text-sm font-medium text-gray-700 mb-1">Título do Evento</label>
            <input type="text" name="titulo" id="titulo" value="{{ old('titulo') }}"
                   class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('titulo') border-red-500 @enderror">
            @error('titulo')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição do Evento</label>
            <textarea name="descricao" id="descricao" rows="4"
                      class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao') }}</textarea>
            @error('descricao')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="data_evento" class="block text-sm font-medium text-gray-700 mb-1">Data</label>
            <input type="date" name="data_evento" id="data_evento" value="{{ old('data_evento') }}"
                   class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('data_evento') border-red-500 @enderror">
            @error('data_evento')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            Criar Evento
        </button>
    </form>
</div>
@endsection