<?php

namespace App\Http\Controllers;

use App\Models\Pergunta;

class PerguntaController extends Controller
{
    /**
     * TICKET #011: registra ou remove o voto do usuário autenticado na pergunta.
     */
    public function votar(Pergunta $pergunta)
    {
        $resultado = $pergunta->votos()->toggle(auth()->id());

        $mensagem = count($resultado['attached']) > 0
            ? 'Seu voto foi registrado!'
            : 'Seu voto foi removido.';

        return back()->with('sucesso', $mensagem);
    }
}
