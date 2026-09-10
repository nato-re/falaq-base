<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerguntaTest extends TestCase
{
    use RefreshDatabase;

    private function criarEvento(): Evento
    {
        return Evento::create([
            'titulo'      => 'Evento de Teste',
            'descricao'   => 'Descrição',
            'data_evento' => now(),
        ]);
    }

    /** TICKET #001: envio vazio deve ser rejeitado com 422. */
    public function test_pergunta_vazia_e_rejeitada(): void
    {
        $evento = $this->criarEvento();

        $resposta = $this->postJson(route('eventos.perguntas.store', $evento->id), [
            'texto' => '',
        ]);

        $resposta->assertStatus(422);
        $resposta->assertJsonValidationErrors('texto');
        $this->assertDatabaseCount('perguntas', 0);
    }

    /** TICKET #001: texto com menos de 10 caracteres deve ser rejeitado. */
    public function test_pergunta_curta_e_rejeitada(): void
    {
        $evento = $this->criarEvento();

        $resposta = $this->postJson(route('eventos.perguntas.store', $evento->id), [
            'texto' => 'oi',
        ]);

        $resposta->assertStatus(422)->assertJsonValidationErrors('texto');
    }

    /** TICKET #001: pergunta válida é persistida. */
    public function test_pergunta_valida_e_salva(): void
    {
        $evento = $this->criarEvento();

        $resposta = $this->post(route('eventos.perguntas.store', $evento->id), [
            'texto' => 'Esta é uma pergunta válida com mais de dez caracteres.',
        ]);

        $resposta->assertRedirect(route('eventos.show', $evento->id));
        $this->assertDatabaseHas('perguntas', [
            'evento_id' => $evento->id,
            'texto'     => 'Esta é uma pergunta válida com mais de dez caracteres.',
        ]);
    }

    /** TICKET #002: a listagem é paginada em 10 e isolada por evento. */
    public function test_perguntas_sao_paginadas_por_evento(): void
    {
        $evento = $this->criarEvento();
        $outro  = $this->criarEvento();

        foreach (range(1, 25) as $i) {
            Pergunta::create([
                'evento_id' => $evento->id,
                'texto'     => "Pergunta numero {$i} do evento principal.",
                'status'    => 'pendente',
            ]);
        }
        Pergunta::create([
            'evento_id' => $outro->id,
            'texto'     => 'Pergunta de outro evento que nao deve aparecer.',
            'status'    => 'pendente',
        ]);

        $resposta = $this->get(route('eventos.show', $evento->id));

        $resposta->assertOk();
        $perguntas = $resposta->viewData('perguntas');
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $perguntas);
        $this->assertCount(10, $perguntas);
        $this->assertEquals(25, $perguntas->total());
        $resposta->assertDontSee('Pergunta de outro evento que nao deve aparecer.');
    }
}
