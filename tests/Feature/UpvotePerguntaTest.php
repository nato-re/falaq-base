<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpvotePerguntaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function criarPergunta(?User $autor = null, ?Evento $evento = null, string $texto = 'Pergunta de teste com mais de dez caracteres'): Pergunta
    {
        $autor ??= User::factory()->create();
        $evento ??= Evento::create(['titulo' => 'Evento', 'user_id' => $autor->id]);

        return Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => $autor->id,
            'texto' => $texto,
        ]);
    }

    public function test_relacionamentos_retornam_belongs_to_many(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();

        $this->assertInstanceOf(BelongsToMany::class, $pergunta->votos());
        $this->assertInstanceOf(BelongsToMany::class, $user->perguntasVotadas());
    }

    public function test_usuario_anonimo_nao_pode_votar(): void
    {
        $pergunta = $this->criarPergunta();

        $response = $this->post(route('perguntas.votar', $pergunta));

        $response->assertRedirect(route('login.create'));
        $this->assertDatabaseCount('pergunta_user', 0);
    }

    public function test_usuario_logado_vota_e_registro_entra_na_pivot(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta))->assertRedirect();

        $this->assertDatabaseHas('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_segundo_clique_remove_o_voto_toggle(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));
        $this->actingAs($user)->post(route('perguntas.votar', $pergunta));

        $this->assertDatabaseMissing('pergunta_user', [
            'pergunta_id' => $pergunta->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_voto_duplicado_direto_no_banco_e_bloqueado_pela_chave_unica(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();

        $pergunta->votos()->attach($user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $pergunta->votos()->attach($user->id);
    }

    public function test_perguntas_do_evento_sao_ordenadas_por_votos_decrescente(): void
    {
        $dono = User::factory()->create();
        $evento = Evento::create(['titulo' => 'Evento', 'user_id' => $dono->id]);

        $semVotos = $this->criarPergunta($dono, $evento, 'Pergunta sem nenhum voto aqui');
        $umVoto = $this->criarPergunta($dono, $evento, 'Pergunta com um voto apenas');
        $doisVotos = $this->criarPergunta($dono, $evento, 'Pergunta com dois votos aqui');

        $a = User::factory()->create();
        $b = User::factory()->create();
        $umVoto->votos()->attach($a->id);
        $doisVotos->votos()->attach([$a->id, $b->id]);

        $response = $this->get(route('eventos.show', $evento->id));

        $response->assertOk();
        $ids = $response->viewData('perguntas')->pluck('id')->all();
        $this->assertSame([$doisVotos->id, $umVoto->id, $semVotos->id], $ids);
        $this->assertSame(2, $response->viewData('perguntas')->first()->votos_count);
    }

    public function test_view_exibe_quantidade_de_votos_e_botao_de_upvote(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('eventos.show', $pergunta->evento_id))
            ->assertOk()
            ->assertSee('0 voto(s)')
            ->assertSee(route('perguntas.votar', $pergunta), false);
    }

    public function test_botao_fica_destacado_quando_usuario_ja_votou(): void
    {
        $pergunta = $this->criarPergunta();
        $user = User::factory()->create();
        $pergunta->votos()->attach($user->id);

        $this->actingAs($user)
            ->get(route('eventos.show', $pergunta->evento_id))
            ->assertOk()
            ->assertSee('1 voto(s)')
            ->assertSee('Votado');
    }
}