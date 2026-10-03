@extends('layouts.app')

@section('title', 'Login — FalaQ')

@section('content')
<form action="{{ route('login.store') }}" method="post" class="bg-black p-6 rounded shadow-md w-96 mx-auto">
    @csrf
    <h2 class="text-2xl font-bold mb-4">Login </h2>
    <div class="mb-4">
        @error('loginError')
            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
        @enderror
        <label for="name">Nome</label>
        <input type="text" name="name" value="{{ old('name') }}" class="w-full mt-1 p-2 border rounded @error('loginError') border-red-500 @enderror"> 


        <label for="password">Senha</label>
        <input type="text" name="password" value="{{ old('password') }}" class="w-full mt-1 p-2 border rounded @error('loginError') border-red-500 @enderror"> 

        <x-button>Entrar</x-button>

    </div>
</form>
@endsection
