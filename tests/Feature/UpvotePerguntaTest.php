<?php

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

function criarPergunta(Evento $evento, string $texto = 'Pergunta de teste com texto suficiente'): Pergunta
{
    return Pergunta::create([
        'evento_id' => $evento->id,
        'texto' => $texto,
        'status' => 'aprovado',
        'is_public' => true,
    ]);
}

beforeEach(function () {
    $this->withoutVite();
});

it('cria a tabela pivot pergunta_user', function () {
    expect(Schema::hasTable('pergunta_user'))->toBeTrue()
        ->and(Schema::hasColumns('pergunta_user', ['id', 'pergunta_id', 'user_id', 'created_at', 'updated_at']))->toBeTrue();
});

it('impede voto duplicado pela chave única composta', function () {
    $user = User::factory()->create();
    $pergunta = criarPergunta(Evento::create(['titulo' => 'Evento']));

    $pergunta->votos()->attach($user->id);
    $pergunta->votos()->attach($user->id);
})->throws(QueryException::class);

it('relaciona votos e perguntasVotadas nos dois sentidos', function () {
    $user = User::factory()->create();
    $pergunta = criarPergunta(Evento::create(['titulo' => 'Evento']));

    $pergunta->votos()->attach($user->id);

    expect($pergunta->votos->contains($user))->toBeTrue()
        ->and($user->perguntasVotadas->contains($pergunta))->toBeTrue()
        ->and($pergunta->votos->first()->pivot->created_at)->not->toBeNull();
});

it('registra o voto e remove no segundo clique', function () {
    $user = User::factory()->create();
    $evento = Evento::create(['titulo' => 'Evento']);
    $pergunta = criarPergunta($evento);

    $this->actingAs($user)
        ->from(route('eventos.show', $evento->id))
        ->post(route('perguntas.votar', $pergunta))
        ->assertRedirect(route('eventos.show', $evento->id))
        ->assertSessionHas('sucesso', 'Seu voto foi registrado!');

    $this->assertDatabaseHas('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);

    $this->actingAs($user)
        ->post(route('perguntas.votar', $pergunta))
        ->assertSessionHas('sucesso', 'Seu voto foi removido.');

    $this->assertDatabaseMissing('pergunta_user', ['pergunta_id' => $pergunta->id, 'user_id' => $user->id]);
});

it('bloqueia visitante não autenticado', function () {
    $pergunta = criarPergunta(Evento::create(['titulo' => 'Evento']));

    $this->post(route('perguntas.votar', $pergunta))
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('pergunta_user', 0);
});

it('aceita apenas POST na rota de votar', function () {
    $user = User::factory()->create();
    $pergunta = criarPergunta(Evento::create(['titulo' => 'Evento']));

    $this->actingAs($user)
        ->get("/perguntas/{$pergunta->id}/votar")
        ->assertStatus(405);
});

it('ordena as perguntas públicas do evento por votos', function () {
    $evento = Evento::create(['titulo' => 'Evento']);
    $outroEvento = Evento::create(['titulo' => 'Outro evento']);

    $poucoVotada = criarPergunta($evento, 'Pergunta com um voto');
    $maisVotada = criarPergunta($evento, 'Pergunta com tres votos');
    $semVotos = criarPergunta($evento, 'Pergunta sem votos');
    criarPergunta($outroEvento, 'Pergunta de outro evento');
    Pergunta::create(['evento_id' => $evento->id, 'texto' => 'Pergunta privada', 'is_public' => false]);

    $users = User::factory()->count(3)->create();
    $maisVotada->votos()->attach($users->pluck('id'));
    $poucoVotada->votos()->attach($users->first()->id);

    $response = $this->get(route('eventos.show', $evento->id))->assertOk();

    $perguntas = $response->viewData('perguntas');
    expect($perguntas->pluck('id')->all())->toBe([$maisVotada->id, $poucoVotada->id, $semVotos->id])
        ->and($perguntas->pluck('votos_count')->all())->toBe([3, 1, 0]);

    $response->assertSeeInOrder(['Pergunta com tres votos', 'Pergunta com um voto', 'Pergunta sem votos'])
        ->assertDontSee('Pergunta de outro evento')
        ->assertDontSee('Pergunta privada')
        ->assertSee('Entre para votar')
        ->assertDontSee(route('perguntas.votar', $maisVotada));
});

it('destaca o botão quando o usuário já votou', function () {
    $user = User::factory()->create();
    $evento = Evento::create(['titulo' => 'Evento']);
    $votada = criarPergunta($evento, 'Pergunta votada');
    criarPergunta($evento, 'Pergunta nao votada');
    $votada->votos()->attach($user->id);

    $this->actingAs($user)
        ->get(route('eventos.show', $evento->id))
        ->assertOk()
        ->assertSee(route('perguntas.votar', $votada))
        ->assertSee('Votado · remover voto')
        ->assertSee('▲ Votar')
        ->assertDontSee('Entre para votar');
});
