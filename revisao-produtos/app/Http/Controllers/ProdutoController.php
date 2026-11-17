<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdutoRequest;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{
    // R1 — listagem pública
    public function index()
    {
        // with('vendedor') = eager loading: carrega todos os vendedores numa
        // consulta só, em vez de 1 consulta por linha (problema N+1).
        $produtos = Produto::with('vendedor')->get();

        return view('produtos.index', compact('produtos'));
    }

    public function create()
    {
        return view('produtos.create');
    }

    // R5 + R6 — cadastro validado com dono
    public function store(StoreProdutoRequest $request)
    {
        // Ao tipar o parâmetro como StoreProdutoRequest, o Laravel valida
        // ANTES de entrar aqui. Se falhar, volta ao form com os erros.
        $dados = $request->validated();

        // R6 — o dono é o usuário logado; nunca vem do formulário
        $dados['vendedor_id'] = Auth::id();

        Produto::create($dados);

        return redirect()->route('produtos.index')->with('sucesso', 'Produto cadastrado!');
    }
}
