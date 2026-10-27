<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ModeracaoController extends Controller
{
    // O Controller base do Laravel 12 não inclui este trait; ele habilita $this->authorize().
    use AuthorizesRequests;

    public const STATUS = ['analisando', 'aprovada', 'rejeitada'];

    /**
     * POST /perguntas/{pergunta}/moderar
     * Participante autenticado solicita a moderação de uma pergunta.
     */
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta)
    {
        $pergunta->moderacoes()->create([
            'motivo' => $request->validated('motivo'),
            'status' => 'analisando',
        ]);

        return back()->with('sucesso', 'Sua solicitação de moderação foi enviada!');
    }

    /**
     * GET /eventos/{evento}/moderacoes
     * Lista as moderações das perguntas do evento (somente quem pode moderar o evento).
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
     * PATCH /moderacoes/{moderacao}/status
     */
    public function updateStatus(Request $request, Moderacao $moderacao)
    {
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', self::STATUS),
        ]);

        $moderacao->update($validated);

        return back()->with('sucesso', 'Status da moderação atualizado com sucesso!');
    }
}
