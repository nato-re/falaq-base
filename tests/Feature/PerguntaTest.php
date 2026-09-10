<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerguntaTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejeita_pergunta_sem_texto(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $response = $this->post(route('eventos.perguntas.store', $evento), [
            'evento_id' => $evento->id,
            'texto' => '',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('texto');

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_rejeita_pergunta_vazia_com_status_422_em_json(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $response = $this->postJson(route('eventos.perguntas.store', $evento), [
            'evento_id' => $evento->id,
            'texto' => '',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('texto');
    }

    public function test_lista_somente_perguntas_do_evento_em_paginas_de_dez(): void
    {
        $evento = Evento::create(['titulo' => 'Evento principal']);
        $outroEvento = Evento::create(['titulo' => 'Outro evento']);

        for ($indice = 1; $indice <= 12; $indice++) {
            Pergunta::forceCreate([
                'evento_id' => $evento->id,
                'texto' => "Pergunta número {$indice}",
                'status' => 'pendente',
                'created_at' => now()->addSeconds($indice),
                'updated_at' => now()->addSeconds($indice),
            ]);
        }

        Pergunta::create([
            'evento_id' => $outroEvento->id,
            'texto' => 'Pergunta de outro evento',
            'status' => 'pendente',
        ]);

        $response = $this->get(route('eventos.show', $evento));

        $response->assertOk()
            ->assertViewHas('perguntas', function ($perguntas) use ($evento) {
                return $perguntas->perPage() === 10
                    && $perguntas->total() === 12
                    && $perguntas->every(fn ($pergunta) => $pergunta->evento_id === $evento->id)
                    && $perguntas->first()->texto === 'Pergunta número 12';
            })
            ->assertSee('pagination')
            ->assertDontSee('Pergunta de outro evento');
    }
}
