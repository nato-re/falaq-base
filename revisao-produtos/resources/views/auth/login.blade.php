@extends('layouts.app')

@section('title', 'Entrar — Loja de Produtos')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm p-4 mb-4">
            <h3 class="fw-bold mb-3">Entrar</h3>
            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label text-secondary">Senha</label>
                    <input type="password" name="password" id="password"
                           class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-bold">Entrar</button>
            </form>
        </div>

        <div class="card p-3">
            <h6 class="fw-bold">Usuários de teste (senha: <code>senha123</code>)</h6>
            <table class="table table-dark table-sm mb-0">
                <tr><td>ana@loja.test</td><td>vendedor</td></tr>
                <tr><td>bruno@loja.test</td><td>vendedor</td></tr>
                <tr><td>carla@loja.test</td><td>cliente</td></tr>
                <tr><td>diego@loja.test</td><td>cliente</td></tr>
            </table>
        </div>
    </div>
</div>
@endsection
