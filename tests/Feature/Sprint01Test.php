<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class Sprint01Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public static function invalidQuestions(): array
    {
        return [
            'sem texto' => [[], 'texto'],
            'texto vazio' => [['texto' => ''], 'texto'],
            'apenas espacos' => [['texto' => '          '], 'texto'],
            'uma letra' => [['texto' => 'a'], 'texto'],
            'nove caracteres' => [['texto' => '123456789'], 'texto'],
            'texto numerico' => [['texto' => 1234567890], 'texto'],
            'texto como lista' => [['texto' => ['pergunta']], 'texto'],
            '256 caracteres' => [['texto' => str_repeat('a', 256)], 'texto'],
            'sem evento' => [['texto' => 'Pergunta valida', 'evento_id' => null], 'evento_id'],
            'evento inexistente' => [['texto' => 'Pergunta valida', 'evento_id' => 999999], 'evento_id'],
        ];
    }

    #[DataProvider('invalidQuestions')]
    public function test_invalid_question_returns_422_without_persisting(array $data, string $field): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);

        $this->actingAs(User::factory()->create())
            ->postJson(route('eventos.perguntas.store', $evento->id), array_replace([
                'evento_id' => $evento->id,
            ], $data))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);

        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_valid_question_accepts_both_length_boundaries(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);
        $user = User::factory()->create();

        foreach ([10, 255] as $length) {
            $texto = str_repeat('a', $length);

            $this->actingAs($user)
                ->post(route('eventos.perguntas.store', $evento->id), [
                    'evento_id' => $evento->id,
                    'texto' => $texto,
                ])
                ->assertRedirect(route('eventos.show', $evento->id));

            $this->assertDatabaseHas('perguntas', [
                'evento_id' => $evento->id,
                'user_id' => $user->id,
                'texto' => $texto,
            ]);
        }

        $this->assertDatabaseCount('perguntas', 2);
    }

    public function test_event_questions_are_filtered_ordered_and_paginated(): void
    {
        $evento = Evento::create(['titulo' => 'Evento de teste']);
        $outroEvento = Evento::create(['titulo' => 'Outro evento']);
        $ids = [];

        for ($i = 1; $i <= 12; $i++) {
            $pergunta = Pergunta::create([
                'evento_id' => $evento->id,
                'texto' => sprintf('Pergunta numero %02d', $i),
            ]);
            $pergunta->created_at = now()->subMinutes(13 - $i);
            $pergunta->save();
            $ids[] = $pergunta->id;
        }

        Pergunta::create([
            'evento_id' => $outroEvento->id,
            'texto' => 'Pergunta exclusiva de outro evento',
        ]);

        DB::enableQueryLog();
        DB::flushQueryLog();

        $response = $this->get(route('eventos.show', $evento->id));

        $response->assertOk()
            ->assertSee('Total de perguntas: 12')
            ->assertSee('name="evento_id"', false)
            ->assertSee('?page=2', false)
            ->assertSee('pagination', false)
            ->assertDontSee('Pergunta exclusiva de outro evento')
            ->assertDontSee('Pergunta numero 01')
            ->assertViewHas('perguntas', function ($perguntas) use ($ids) {
                return $perguntas->perPage() === 10
                    && $perguntas->total() === 12
                    && $perguntas->pluck('id')->all() === array_slice(array_reverse($ids), 0, 10);
            });

        $queries = array_filter(DB::getQueryLog(), fn ($query) => str_contains($query['query'], '"perguntas"'));
        $this->assertCount(2, $queries);

        $this->get(route('eventos.show', $evento->id).'?page=2')
            ->assertOk()
            ->assertViewHas('perguntas', fn ($perguntas) => $perguntas->pluck('id')->all() === [$ids[1], $ids[0]])
            ->assertSee('Pergunta numero 01')
            ->assertDontSee('Pergunta numero 12')
            ->assertDontSee('Pergunta exclusiva de outro evento');
    }

    public function test_missing_event_returns_404(): void
    {
        $this->get(route('eventos.show', 999999))->assertNotFound();
    }
}
