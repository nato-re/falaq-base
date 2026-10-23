<?php

namespace App\Http\Controllers;

use App\Enums\EventoStatus;
use App\Http\Requests\EventoFormRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $eventos = Evento::where('status', $request->status ?? 'publicado')->get();
        $title = $request->status === EventoStatus::PUBLICADO->value ? "Ativos" : "Encerrados";
        return view('eventos.index', compact('eventos', 'title'));
    }

    public function userEventos()
    {
        $eventos = Auth::user()->eventos;
        return view('eventos.index', compact('eventos'));
    }

    public function show($id)
    {
        $evento = Evento::find($id);

        $perguntas = Pergunta::all();

        return view('eventos.show', compact('evento', 'perguntas'));
    }

 
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

    public function create(){
        return view('eventos.create');
    }

    public function store(EventoFormRequest $request){
        $evento = $request->user()->eventos()->create($request->validated());
        return redirect()->route('eventos.show', $evento->id);
    }

    public function updateStatus(Request $request, Evento $evento)
{
    // dd($request, $evento);
    $request->validate([
        'status' => ['required', new Enum(EventoStatus::class)],
    ]);

    $evento->update([
        'status' => $request->status,
    ]);

    return back()->with('status', 'Status do evento atualizado com sucesso!');
}
}
