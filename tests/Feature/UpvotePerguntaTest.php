<?php

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function criarPergunta(Evento $evento, string $texto = 'Pergunta de teste'): Pergunta
{
    return Pergunta::create([
        'evento_id' => $evento->id,
        'texto' => $texto,
        'status' => 'pendente',
    ]);
}

beforeEach(function () {
    $this->evento = Evento::create(['titulo' => 'Evento de Teste']);
    $this->pergunta = criarPergunta($this->evento);
});

it('relaciona votos entre Pergunta e User pela tabela pergunta_user', function () {
    $user = User::factory()->create();

    $this->pergunta->votos()->attach($user->id);

    expect($this->pergunta->votos()->pluck('users.id')->all())->toBe([$user->id])
        ->and($user->perguntasVotadas()->pluck('perguntas.id')->all())->toBe([$this->pergunta->id])
        ->and($this->pergunta->votos()->first()->pivot->created_at)->not->toBeNull();
});

it('impede voto duplicado pela restrição única (pergunta_id, user_id)', function () {
    $user = User::factory()->create();

    DB::table('pergunta_user')->insert(['pergunta_id' => $this->pergunta->id, 'user_id' => $user->id]);
    DB::table('pergunta_user')->insert(['pergunta_id' => $this->pergunta->id, 'user_id' => $user->id]);
})->throws(QueryException::class);

it('remove os votos quando a pergunta é apagada (cascade)', function () {
    $user = User::factory()->create();
    $this->pergunta->votos()->attach($user->id);

    $this->pergunta->delete();

    expect(DB::table('pergunta_user')->count())->toBe(0);
});

it('impede visitante anônimo de votar', function () {
    $this->post(route('perguntas.votar', $this->pergunta))
        ->assertRedirect(route('login.create'));

    expect($this->pergunta->votos()->count())->toBe(0);
});

it('permite que usuário autenticado vote e volte para a página anterior', function () {
    $user = User::factory()->create();
    $origem = route('eventos.show', $this->evento->id);

    $this->actingAs($user)
        ->from($origem)
        ->post(route('perguntas.votar', $this->pergunta))
        ->assertRedirect($origem)
        ->assertSessionHas('status');

    expect($this->pergunta->votos()->whereKey($user->id)->exists())->toBeTrue();
});

it('remove o voto ao votar novamente (toggle)', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('perguntas.votar', $this->pergunta));
    $this->actingAs($user)->post(route('perguntas.votar', $this->pergunta));

    expect($this->pergunta->votos()->count())->toBe(0);
});

it('exibe as perguntas do evento ordenadas por número de votos', function () {
    [$a, $b, $c] = User::factory()->count(3)->create();

    $popular = criarPergunta($this->evento, 'Pergunta mais votada');
    $media = criarPergunta($this->evento, 'Pergunta com um voto');
    $popular->votos()->attach([$a->id, $b->id, $c->id]);
    $media->votos()->attach($a->id);

    $outroEvento = Evento::create(['titulo' => 'Outro evento']);
    criarPergunta($outroEvento, 'Pergunta de outro evento');

    $this->get(route('eventos.show', $this->evento->id))
        ->assertOk()
        ->assertSeeInOrder(['Pergunta mais votada', 'Pergunta com um voto', 'Pergunta de teste'])
        ->assertDontSee('Pergunta de outro evento')
        ->assertDontSee('▲ Votar');
});

it('indica quando o usuário autenticado já votou', function () {
    $user = User::factory()->create();
    $this->pergunta->votos()->attach($user->id);

    $this->actingAs($user)
        ->get(route('eventos.show', $this->evento->id))
        ->assertOk()
        ->assertSee('✓ Votado')
        ->assertSee(route('perguntas.votar', $this->pergunta), false);
});

it('não faz uma consulta por pergunta ao montar o ranking', function () {
    $user = User::factory()->create();
    foreach (range(1, 10) as $i) {
        criarPergunta($this->evento, "Pergunta {$i}")->votos()->attach($user->id);
    }

    DB::enableQueryLog();
    $this->actingAs($user)->get(route('eventos.show', $this->evento->id))->assertOk();
    $consultas = count(DB::getQueryLog());

    expect($consultas)->toBeLessThan(10);
});
