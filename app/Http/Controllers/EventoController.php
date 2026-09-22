<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\EventoFormRequest;
use App\Http\Requests\StorePerguntaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        return view('eventos.index', compact('eventos'));
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

    public function show(Evento $evento)
    {
        // Ao usar os parênteses em perguntas(), inicias o Query Builder
        $perguntas = $evento->perguntas()
            ->with('user') // Mantém o Eager Loading
            ->where('is_public', true) // Filtra as perguntas pendentes
            ->paginate(10); // Mantém a paginação

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    /**
     * TICKET #001 (BUG LEGADO DE SEGURANÇA):
     * Salva a pergunta usando a requisição sem validações rigorosas.
     */
    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => Auth::user()->id,
            'texto'     => $request->input('texto'),
            'status'    => 'pendente',
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }
}