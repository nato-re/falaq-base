<?php

/*
|--------------------------------------------------------------------------
| Pest — configuração dos testes
|--------------------------------------------------------------------------
|
| Os testes de Feature usam o TestCase do Laravel (para ter $this->get(),
| $this->actingAs(), etc.) e recriam o banco SQLite em memória a cada teste.
| withoutVite() evita depender do build do front-end (public/build).
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->beforeEach(fn () => $this->withoutVite())
    ->in('Feature');
