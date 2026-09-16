<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();

        return view('eventos.index', compact('eventos'));
    }

    /**
     * TICKET #002 / #004:
     * Filtra as perguntas do evento atual,
     * carrega o usuário de cada pergunta,
     * ordena pelas mais recentes
     * e pagina de 10 em 10.
     */
    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        $perguntas = Pergunta::with('user')
            ->where('evento_id', $evento->id)
            ->latest()
            ->paginate(10);

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    /**
     * TICKET #001:
     * Salva a pergunta usando a requisição validada.
     */
    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'texto'     => $request->input('texto'),
            'status'    => 'pendente',
        ]);

        return redirect()
            ->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }
}