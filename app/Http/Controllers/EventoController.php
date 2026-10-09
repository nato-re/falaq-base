<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoFormRequest;
use App\Http\Requests\StorePerguntaRequest;
use App\Models\Evento;
use App\Models\Pergunta;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();

        return view('eventos.index', compact('eventos'));
    }

    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        $perguntas = Pergunta::with('user')
            ->where('evento_id', $evento->id)
            ->where('is_public', true)
            ->withCount('votos')
            ->when(auth()->check(), function ($query) {
                $query->withExists([
                    'votos as votada_pelo_usuario' => function ($q) {
                        $q->where('users.id', auth()->id());
                    }
                ]);
            })
            ->orderByDesc('votos_count')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => $request->user()->id,
            'texto' => $request->input('texto'),
            'status' => 'pendente',
        ]);

        return redirect()
            ->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }

    public function create()
    {
        return view('eventos.create');
    }

    public function store(EventoFormRequest $req)
    {
        $evento = $req->user()->eventos()->create($req->validated());

        return redirect()->route('eventos.show', $evento->id);
    }
}