<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Venda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompraController extends Controller
{
    // R8 — minhas compras (só do usuário logado)
    public function index()
    {
        $vendas = Venda::with('produtos')
            ->where('cliente_id', Auth::id())
            ->get();

        return view('compras.index', compact('vendas'));
    }

    public function create()
    {
        $produtos = Produto::orderBy('nome')->get();

        return view('compras.create', compact('produtos'));
    }

    // R7 — compra  (+ Bônus — estoque)
    public function store(Request $request)
    {
        // O form manda itens[id_do_produto] = quantidade, ex.: [1 => 2, 2 => 0, 3 => 1]
        // R7.1 — array_filter tira os itens com quantidade 0
        $itens = array_filter($request->input('itens', []), fn ($qtd) => $qtd > 0);

        if (empty($itens)) {
            return back()->withErrors(['itens' => 'Escolha ao menos um produto.']);
        }

        // BÔNUS — confere o estoque de TODOS antes de criar qualquer coisa
        foreach ($itens as $produtoId => $quantidade) {
            $produto = Produto::findOrFail($produtoId);
            if ($quantidade > $produto->estoque) {
                return back()
                    ->withErrors(['itens' => "Estoque insuficiente para {$produto->nome}."])
                    ->withInput();
            }
        }

        // R7.2 — cria a venda com o cliente logado
        $venda = Venda::create(['cliente_id' => Auth::id()]);

        foreach ($itens as $produtoId => $quantidade) {
            $produto = Produto::findOrFail($produtoId);

            // R7.3 — grava a linha na pivô produto_venda com os campos extras
            $venda->produtos()->attach($produto->id, [
                'quantidade'     => $quantidade,
                'preco_unitario' => $produto->preco, // preço ATUAL do produto
            ]);

            // BÔNUS — baixa o estoque
            $produto->decrement('estoque', $quantidade);
        }

        // R7.4
        return redirect()->route('compras.index')->with('sucesso', 'Compra realizada!');
    }
}
