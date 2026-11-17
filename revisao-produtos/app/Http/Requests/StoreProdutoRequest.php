<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// R5 — criado com: php artisan make:request StoreProdutoRequest
class StoreProdutoRequest extends FormRequest
{
    // O make:request gera "return false;" — PRECISA trocar para true,
    // senão toda requisição dá 403. (Quem controla o acesso é o middleware.)
    public function authorize(): bool
    {
        return true;
    }

    // Regras = tabela de contratos do README
    public function rules(): array
    {
        return [
            'nome'           => 'required|string|max:255',
            'categoria'      => 'required|in:Eletrônicos,Vestuário,Casa,Alimentos',
            'garantia_meses' => 'required|integer|between:0,36',
            'preco'          => 'required|numeric|gt:0',
            'estoque'        => 'required|integer|min:0',
            // vendedor_id NÃO entra aqui: não vem do formulário (R6)
        ];
    }
}
