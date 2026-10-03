@extends('layouts.app')

@section('title', 'Eventos — FalaQ')

@section('content')
<form action="{{ route('register.store') }}" method="post" class="bg-black p-6 rounded shadow-md w-96 mx-auto">
    @csrf
    <h2 class="text-2xl font-bold mb-4">Criar Conta</h2>
    <div class="mb-4">
        <x-input label="Nome" name="name" />
        <x-input label="Email" name="email" />
        <x-input label="Senha" name="password" type="password"/>
        <x-button>Criar</x-button>

    </div>
</form>
@endsection
