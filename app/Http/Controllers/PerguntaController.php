<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;

class PerguntaController extends Controller
{
    public function votar(Pergunta $pergunta)
    {
        $pergunta->votos()->toggle(auth()->id());

        return back()->with('status', 'Voto atualizado com sucesso!');
    }
}