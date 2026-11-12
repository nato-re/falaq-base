@extends('layouts.app')

@section('title', $evento->titulo . ' — FalaQ')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $evento->titulo }}</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Formulário de envio de Pergunta (TICKETS #007 e #008) -->
        <div class="md:col-span-5">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h4 class="text-lg font-bold text-gray-800 dark:text-white mb-4">💬 Faça sua Pergunta</h4>

                <form action="{{ route('eventos.perguntas.store', $evento->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="evento_id" value="{{ $evento->id }}">

                    <div class="mb-4">
                        <label for="texto" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Texto da Pergunta
                        </label>

                        <textarea
                            name="texto"
                            id="texto"
                            rows="4"
                            placeholder="Digite sua dúvida ou comentário para o palestrante..."
                            class="w-full p-3 rounded-md border border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('texto') border-red-500 @enderror"
                        >{{ old('texto') }}</textarea>

                        @error('texto')
                            <p class="text-red-500 text-sm font-semibold mt-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition font-bold"
                    >
                        Enviar Pergunta
                    </button>
                </form>
            </div>
        </div>

        <!-- Lista de Perguntas / Mural estilo balão de chat (TICKETS #002 e #008) -->
        <div class="md:col-span-7">
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-bold text-gray-800 dark:text-white m-0">📋 Perguntas do Evento</h4>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    Total no Banco: {{ $evento->perguntas->count() }}
                </span>
            </div>

            @forelse($perguntas as $pergunta)
                <div class="bg-white dark:bg-gray-800 border-l-4 border-blue-600 p-4 rounded-2xl rounded-tl-none shadow-sm mb-4">
                    <p class="text-base text-gray-900 dark:text-white mb-2">
                        {{ $pergunta->texto }}
                    </p>
                    <div class="flex justify-between items-center text-xs text-gray-500 dark:text-gray-400">
                        <span>
                            Status:
                            <span class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-0.5 rounded-full font-semibold">
                                {{ $pergunta->status }}
                            </span>
                        </span>
                        <span>{{ $pergunta->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            @empty
                <div class="bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 text-center p-6 rounded-lg">
                    Nenhuma pergunta enviada ainda. Seja o primeiro!
                </div>
            @endforelse

            <!-- Renderização dos Botões de Paginação -->
            @if(method_exists($perguntas, 'links'))
                <div class="flex justify-center mt-6">
                    {{ $perguntas->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
