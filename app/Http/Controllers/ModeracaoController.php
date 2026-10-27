<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModeracaoController extends Controller
{
    public function store(
        StoreModeracaoRequest $request,
        Pergunta $pergunta
    ) {
        $pergunta->moderacoes()->create([
            'motivo' => $request->validated('motivo'),
        ]);

        return back()->with(
            'sucesso',
            'Denúncia enviada para análise.'
        );
    }

    public function index(Evento $evento)
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', function ($query) use ($evento) {
                $query->where('evento_id', $evento->id);
            })
            ->latest()
            ->paginate(15);

        return view(
            'moderacoes.index',
            compact('evento', 'moderacoes')
        );
    }

    public function updateStatus(
        Request $request,
        Moderacao $moderacao
    ) {
        $this->authorize(
            'moderate',
            $moderacao->pergunta->evento
        );

        $dados = $request->validate([
            'status' => [
                'required',
                Rule::in(['aprovado', 'rejeitado']),
            ],
        ]);

        DB::transaction(function () use ($moderacao, $dados) {
            $moderacao->update([
                'status' => $dados['status'],
            ]);

            $moderacao->pergunta->update([
                'status' => $dados['status'],
                'is_public' => $dados['status'] === 'aprovado',
            ]);
        });

        return redirect()
            ->route(
                'moderacoes.index',
                $moderacao->pergunta->evento_id
            )
            ->with('sucesso', 'Decisão de moderação registrada.');
    }
}