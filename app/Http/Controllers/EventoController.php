<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoFormRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PhpParser\Builder\Use_;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::withCount('participantes')
            ->orderByDesc('participantes_count')
            ->get();
        return view('eventos.index', compact('eventos'));
    }

    public function show(string $id)
    {
        $evento = Evento::findOrFail($id);

        // Ranking: apenas as perguntas deste evento, com a contagem de votos
        // e um flag "votou" do usuário logado, tudo numa única consulta (sem N+1).
        $perguntas = $evento->perguntas()
            ->withCount('votos')
            ->withExists(['votos as votou' => fn ($query) => $query->where('users.id', Auth::id())])
            ->orderByDesc('votos_count')
            ->orderBy('created_at')
            ->get();

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

    public function toggleInscricao(Evento $evento){
        // dd($evento);
        $evento->participantes()->toggle(Auth::id());
        // $evento->participantes()->attach(Auth::id());
        // $user = User::find(Auth::id());
        // $user->eventosInscritos()->attach($evento->id);
        return back()->with('status', 'Inscrição atualizada com sucesso!');
    }

    public function votar(Pergunta $pergunta)
    {
        $resultado = $pergunta->votos()->toggle(auth()->id());

        $mensagem = count($resultado['attached']) > 0
            ? 'Voto registrado com sucesso!'
            : 'Voto removido com sucesso!';

        return back()->with('status', $mensagem);
    }

}
