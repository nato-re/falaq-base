@extends('layouts.app')

@section('title', 'Criar Evento — FalaQ')

@section('content')
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md text-gray-900">
        <h2 class="text-2xl font-bold mb-6">Criar Evento</h2>

        <form action="{{ route('eventos.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="titulo" class="block mb-2 text-sm font-medium">Título do evento</label>
                <input
                    type="text"
                    id="titulo"
                    name="titulo"
                    value="{{ old('titulo') }}"
                    class="w-full rounded-md border p-2 @error('titulo') border-red-500 @else border-gray-300 @enderror"
                >

                @error('titulo')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="descricao" class="block mb-2 text-sm font-medium">Descrição</label>
                <textarea
                    id="descricao"
                    name="descricao"
                    rows="5"
                    class="w-full rounded-md border p-2 @error('descricao') border-red-500 @else border-gray-300 @enderror"
                >{{ old('descricao') }}</textarea>

                @error('descricao')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">
                Criar evento
            </button>
        </form>
    </div>
@endsection