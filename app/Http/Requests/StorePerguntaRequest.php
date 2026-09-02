<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguntaRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara os dados para validação, injetando o evento_id a partir da rota.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['evento_id' => $this->route('id')]);
    }

    /**
     * TICKET #001: Implemente aqui as regras de validação estritas.
     * Requisitos:
     * - texto: obrigatório, string, mínimo de 10 caracteres, máximo de 255.
     * - evento_id: obrigatório, deve existir na tabela eventos.
     */
    public function rules(): array
    {
        return [
            'texto' => ['required', 'string', 'min:10', 'max:255'],
            'evento_id' => ['required', 'exists:eventos,id'],
        ];
    }
}
