<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sprint 07 — Moderação de perguntas (Tickets #013 a #016).
 * Usa o SQLite em memória definido no phpunit.xml (não toca no banco local).
 */
class ModeracaoTest extends TestCase
{
    use RefreshDatabase;

    private User $dono;
    private User $participante;
    private Evento $evento;
    private Pergunta $pergunta;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->dono = User::factory()->create();
        $this->participante = User::factory()->create();
        $this->evento = Evento::create(['titulo' => 'Evento Teste', 'user_id' => $this->dono->id]);
        $this->pergunta = Pergunta::create([
            'evento_id' => $this->evento->id,
            'user_id' => $this->participante->id,
            'texto' => 'Pergunta de teste para moderação',
        ]);
    }

    private function criarModeracao(): Moderacao
    {
        return $this->pergunta->moderacoes()->create(['motivo' => 'Conteúdo ofensivo na pergunta']);
    }

    public function test_relacionamentos_e_status_padrao(): void
    {
        $moderacao = $this->criarModeracao()->fresh();

        $this->assertSame('analisando', $moderacao->status);
        $this->assertTrue($moderacao->pergunta->is($this->pergunta));
        $this->assertCount(1, $this->pergunta->moderacoes);
    }

    public function test_visitante_nao_pode_solicitar_moderacao(): void
    {
        $this->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Conteúdo ofensivo aqui'])
            ->assertRedirect(route('login.create'));

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_participante_autenticado_solicita_moderacao(): void
    {
        $this->actingAs($this->participante)
            ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Conteúdo ofensivo aqui'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('moderacoes', [
            'pergunta_id' => $this->pergunta->id,
            'motivo' => 'Conteúdo ofensivo aqui',
            'status' => 'analisando',
        ]);
    }

    public function test_motivo_e_validado(): void
    {
        $this->actingAs($this->participante)
            ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'curto'])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($this->participante)
            ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => str_repeat('a', 256)])
            ->assertSessionHasErrors('motivo');

        $this->actingAs($this->participante)
            ->post(route('perguntas.moderar', $this->pergunta), [])
            ->assertSessionHasErrors('motivo');

        $this->assertDatabaseCount('moderacoes', 0);
    }

    public function test_apenas_dono_do_evento_lista_moderacoes(): void
    {
        $this->criarModeracao();

        $this->actingAs($this->participante)
            ->get(route('eventos.moderacoes.index', $this->evento))
            ->assertForbidden();

        $this->actingAs($this->dono)
            ->get(route('eventos.moderacoes.index', $this->evento))
            ->assertOk()
            ->assertSee('Conteúdo ofensivo na pergunta');
    }

    public function test_listagem_nao_mostra_moderacoes_de_outros_eventos(): void
    {
        $outroEvento = Evento::create(['titulo' => 'Outro', 'user_id' => $this->participante->id]);
        $outraPergunta = Pergunta::create(['evento_id' => $outroEvento->id, 'texto' => 'Outra pergunta qualquer']);
        $outraPergunta->moderacoes()->create(['motivo' => 'Motivo de outro evento']);

        $this->actingAs($this->dono)
            ->get(route('eventos.moderacoes.index', $this->evento))
            ->assertOk()
            ->assertDontSee('Motivo de outro evento');
    }

    public function test_apenas_dono_do_evento_atualiza_status(): void
    {
        $moderacao = $this->criarModeracao();

        $this->actingAs($this->participante)
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovada'])
            ->assertForbidden();
        $this->assertSame('analisando', $moderacao->fresh()->status);

        $this->actingAs($this->dono)
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovada'])
            ->assertRedirect();
        $this->assertSame('aprovada', $moderacao->fresh()->status);
    }

    public function test_status_invalido_e_rejeitado(): void
    {
        $moderacao = $this->criarModeracao();

        $this->actingAs($this->dono)
            ->patch(route('moderacoes.status', $moderacao), ['status' => 'qualquer'])
            ->assertSessionHasErrors('status');

        $this->assertSame('analisando', $moderacao->fresh()->status);
    }

    public function test_moderacoes_sao_apagadas_junto_com_a_pergunta(): void
    {
        $this->criarModeracao();
        $this->pergunta->delete();

        $this->assertDatabaseCount('moderacoes', 0);
    }
}
