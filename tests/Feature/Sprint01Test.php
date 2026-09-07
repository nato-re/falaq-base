<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Pergunta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint01Test extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_questions_return_422_without_being_saved(): void
    {
        $evento = Evento::create(['titulo' => 'Evento']);
        $url = route('eventos.perguntas.store', $evento->id);

        foreach ([null, '', '   ', 'a', str_repeat('a', 9), str_repeat('a', 256), ['texto'], 123] as $texto) {
            $this->postJson($url, ['evento_id' => $evento->id, 'texto' => $texto])
                ->assertUnprocessable()->assertJsonValidationErrors('texto');
        }

        $this->postJson($url, ['evento_id' => $evento->id])
            ->assertUnprocessable()->assertJsonValidationErrors('texto');

        foreach ([null, 999] as $id) {
            $this->postJson($url, ['evento_id' => $id, 'texto' => 'Uma pergunta válida?'])
                ->assertUnprocessable()->assertJsonValidationErrors('evento_id');
        }

        $this->postJson($url, ['texto' => 'Uma pergunta válida?'])
            ->assertUnprocessable()->assertJsonValidationErrors('evento_id');
        $this->assertDatabaseCount('perguntas', 0);
    }

    public function test_valid_questions_at_both_length_limits_are_saved(): void
    {
        $evento = Evento::create(['titulo' => 'Evento']);

        foreach ([10, 255] as $length) {
            $texto = str_repeat('a', $length);
            $this->post(route('eventos.perguntas.store', $evento->id), [
                'evento_id' => $evento->id,
                'texto' => $texto,
            ])->assertRedirect(route('eventos.show', $evento->id))->assertSessionHasNoErrors();
            $this->assertDatabaseHas('perguntas', ['evento_id' => $evento->id, 'texto' => $texto]);
        }
    }

    public function test_event_questions_are_filtered_ordered_and_paginated(): void
    {
        $evento = Evento::create(['titulo' => 'Evento']);
        $outro = Evento::create(['titulo' => 'Outro']);
        for ($i = 1; $i <= 12; $i++) {
            $pergunta = Pergunta::create(['evento_id' => $evento->id, 'texto' => "Pergunta número $i"]);
            $pergunta->created_at = now()->subMinutes(13 - $i);
            $pergunta->save();
        }
        Pergunta::create(['evento_id' => $outro->id, 'texto' => 'Pergunta de outro evento']);

        $response = $this->get(route('eventos.show', $evento->id));
        $response->assertOk()->assertDontSee('Pergunta de outro evento')
            ->assertSee('page=2')->assertSee('class="pagination"', false)
            ->assertSee('name="evento_id" value="'.$evento->id.'"', false);
        $paginator = $response->viewData('perguntas');
        $this->assertSame(12, $paginator->total());
        $this->assertCount(10, $paginator->items());
        $this->assertSame('Pergunta número 12', $paginator->first()->texto);
        $this->assertSame('Pergunta número 3', $paginator->last()->texto);
        $this->assertFalse($response->viewData('evento')->relationLoaded('perguntas'));

        $page2 = $this->get(route('eventos.show', ['id' => $evento->id, 'page' => 2]));
        $page2->assertOk()->assertSee('page=1');
        $this->assertSame(['Pergunta número 2', 'Pergunta número 1'],
            $page2->viewData('perguntas')->pluck('texto')->all());
    }
}
