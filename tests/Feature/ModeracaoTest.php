<?php

use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->dono = User::factory()->create();
    $this->evento = Evento::create(['titulo' => 'Evento Moderado', 'user_id' => $this->dono->id]);
    $this->pergunta = Pergunta::create([
        'evento_id' => $this->evento->id,
        'texto' => 'Pergunta a ser moderada',
        'status' => 'pendente',
    ]);
});

function criarModeracao(Pergunta $pergunta, string $motivo = 'Conteúdo ofensivo na pergunta'): Moderacao
{
    return $pergunta->moderacoes()->create(['motivo' => $motivo]);
}

// ---------- Ticket #013 / #014: estrutura e relacionamentos ----------

it('cria a tabela moderacoes com as colunas esperadas', function () {
    expect(Schema::hasTable('moderacoes'))->toBeTrue()
        ->and(Schema::hasColumns('moderacoes', ['id', 'pergunta_id', 'motivo', 'status', 'created_at', 'updated_at']))->toBeTrue()
        ->and((new Moderacao)->getTable())->toBe('moderacoes');
});

it('usa "analisando" como status padrão no banco', function () {
    $moderacao = criarModeracao($this->pergunta);

    expect($moderacao->fresh()->status)->toBe('analisando');
});

it('relaciona Moderacao e Pergunta nos dois sentidos', function () {
    $moderacao = criarModeracao($this->pergunta);

    expect($moderacao->pergunta->is($this->pergunta))->toBeTrue()
        ->and($this->pergunta->moderacoes()->pluck('id')->all())->toBe([$moderacao->id]);
});

it('apaga as moderações quando a pergunta é apagada (cascade)', function () {
    criarModeracao($this->pergunta);
    criarModeracao($this->pergunta, 'Outro motivo bem descrito');

    $this->pergunta->delete();

    expect(Moderacao::count())->toBe(0);
});

// ---------- Ticket #015 / #016: store ----------

it('impede visitante anônimo de solicitar moderação', function () {
    $this->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Motivo válido com texto'])
        ->assertRedirect(route('login.create'));

    expect(Moderacao::count())->toBe(0);
});

it('permite que usuário autenticado solicite moderação com status analisando', function () {
    $user = User::factory()->create();
    $origem = route('eventos.show', $this->evento->id);

    $this->actingAs($user)
        ->from($origem)
        ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => 'Pergunta com linguagem ofensiva', 'status' => 'aprovada'])
        ->assertRedirect($origem)
        ->assertSessionHas('sucesso');

    $moderacao = Moderacao::sole();
    expect($moderacao->pergunta_id)->toBe($this->pergunta->id)
        ->and($moderacao->motivo)->toBe('Pergunta com linguagem ofensiva')
        ->and($moderacao->status)->toBe('analisando');
});

it('valida o motivo da moderação', function (mixed $motivo) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('perguntas.moderar', $this->pergunta), ['motivo' => $motivo])
        ->assertSessionHasErrors('motivo');

    expect(Moderacao::count())->toBe(0);
})->with([
    'vazio' => [''],
    'ausente' => [null],
    'curto (9 caracteres)' => [str_repeat('a', 9)],
    'longo (256 caracteres)' => [str_repeat('a', 256)],
]);

it('aceita motivos nos limites de 10 e 255 caracteres', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('perguntas.moderar', $this->pergunta), ['motivo' => str_repeat('a', 10)])
        ->assertSessionHasNoErrors();
    $this->actingAs($user)->post(route('perguntas.moderar', $this->pergunta), ['motivo' => str_repeat('b', 255)])
        ->assertSessionHasNoErrors();

    expect(Moderacao::count())->toBe(2);
});

// ---------- Ticket #016: index ----------

it('exige login para ver a lista de moderações', function () {
    $this->get(route('eventos.moderacoes.index', $this->evento))
        ->assertRedirect(route('login.create'));
});

it('proíbe quem não é dono do evento de ver a lista de moderações', function () {
    $outro = User::factory()->create();

    $this->actingAs($outro)
        ->get(route('eventos.moderacoes.index', $this->evento))
        ->assertForbidden();
});

it('mostra ao dono apenas as moderações do próprio evento', function () {
    criarModeracao($this->pergunta, 'Motivo do evento certo');

    $outroEvento = Evento::create(['titulo' => 'Outro evento', 'user_id' => $this->dono->id]);
    $outraPergunta = Pergunta::create(['evento_id' => $outroEvento->id, 'texto' => 'Pergunta de outro evento']);
    criarModeracao($outraPergunta, 'Motivo de outro evento');

    $resposta = $this->actingAs($this->dono)
        ->get(route('eventos.moderacoes.index', $this->evento))
        ->assertOk()
        ->assertViewIs('moderacoes.index')
        ->assertSee('Pergunta a ser moderada')
        ->assertSee('Motivo do evento certo')
        ->assertDontSee('Motivo de outro evento');

    expect($resposta->viewData('moderacoes')->total())->toBe(1);
});

it('pagina as moderações de 15 em 15, das mais recentes para as mais antigas', function () {
    foreach (range(1, 16) as $i) {
        $this->travel(1)->minutes();
        criarModeracao($this->pergunta, "Motivo número {$i} da lista");
    }

    $pagina = $this->actingAs($this->dono)
        ->get(route('eventos.moderacoes.index', $this->evento))
        ->assertOk()
        ->viewData('moderacoes');

    expect($pagina->count())->toBe(15)
        ->and($pagina->total())->toBe(16)
        ->and($pagina->first()->motivo)->toBe('Motivo número 16 da lista')
        ->and($pagina->first()->relationLoaded('pergunta'))->toBeTrue();
});

// ---------- Ticket #016: updateStatus ----------

it('permite que o dono do evento atualize o status', function () {
    $moderacao = criarModeracao($this->pergunta);

    $this->actingAs($this->dono)
        ->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovada'])
        ->assertRedirect()
        ->assertSessionHas('sucesso');

    expect($moderacao->fresh()->status)->toBe('aprovada');
});

it('proíbe quem não é dono do evento de alterar o status', function () {
    $moderacao = criarModeracao($this->pergunta);
    $outro = User::factory()->create();

    $this->actingAs($outro)
        ->patch(route('moderacoes.status', $moderacao), ['status' => 'rejeitada'])
        ->assertForbidden();

    expect($moderacao->fresh()->status)->toBe('analisando');
});

it('exige login para alterar o status', function () {
    $moderacao = criarModeracao($this->pergunta);

    $this->patch(route('moderacoes.status', $moderacao), ['status' => 'aprovada'])
        ->assertRedirect(route('login.create'));

    expect($moderacao->fresh()->status)->toBe('analisando');
});

it('rejeita status fora da lista permitida', function (mixed $status) {
    $moderacao = criarModeracao($this->pergunta);

    $this->actingAs($this->dono)
        ->patch(route('moderacoes.status', $moderacao), ['status' => $status])
        ->assertSessionHasErrors('status');

    expect($moderacao->fresh()->status)->toBe('analisando');
})->with([
    'inválido' => ['apagada'],
    'vazio' => [''],
]);

it('exibe o link de moderação apenas para o dono do evento', function () {
    $outro = User::factory()->create();

    $this->actingAs($this->dono)
        ->get(route('eventos.show', $this->evento->id))
        ->assertSee(route('eventos.moderacoes.index', $this->evento), false);

    $this->actingAs($outro)
        ->get(route('eventos.show', $this->evento->id))
        ->assertDontSee(route('eventos.moderacoes.index', $this->evento), false)
        ->assertSee(route('perguntas.moderar', $this->pergunta), false);
});
