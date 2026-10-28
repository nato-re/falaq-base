<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModeracaoController extends Controller
{
    // O Controller base do Laravel 12 é vazio; o trait habilita $this->authorize().
    use AuthorizesRequests;

    /**
     * Solicita a moderação de uma pergunta.
     */
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta)
    {
        $pergunta->moderacoes()->create([
            'motivo' => $request->validated('motivo'),
            'status' => 'analisando',
        ]);

        return back()->with('sucesso', 'Pergunta enviada para moderação. Obrigado pelo aviso!');
    }

    /**
     * Lista as moderações das perguntas de um evento (apenas para o dono).
     */
    public function index(Evento $evento)
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', fn ($query) => $query->where('evento_id', $evento->id))
            ->latest()
            ->paginate(15);

        return view('moderacoes.index', compact('evento', 'moderacoes'));
    }

    /**
     * Atualiza o status de uma moderação.
     */
    public function updateStatus(Request $request, Moderacao $moderacao)
    {
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $dados = $request->validate([
            'status' => ['required', Rule::in(Moderacao::STATUS)],
        ]);

        $moderacao->update(['status' => $dados['status']]);

        return back()->with('sucesso', 'Status da moderação atualizado com sucesso!');
    }
}
