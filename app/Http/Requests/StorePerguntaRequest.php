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
     * Adiciona o ID do evento da URL aos dados da requisição
     * antes da validação.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'evento_id' => $this->route('id'),
        ]);
    }

    /**
     * TICKET #001: Regras de validação.
     */
    public function rules(): array
    {
        return [
            'texto' => 'required|string|min:10|max:255',
            'evento_id' => 'required|exists:eventos,id',
        ];
    }
}