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
     * TICKET #001: Regras de validação estritas contra spam.
     * - texto: obrigatório, string, mínimo de 10 caracteres, máximo de 255.
     * - evento_id: obrigatório, deve existir na tabela eventos.
     */
    public function rules(): array
    {
        return [
            'texto'     => ['required', 'string', 'min:10', 'max:255'],
            'evento_id' => ['required', 'exists:eventos,id'],
        ];
    }

    /**
     * O evento_id vem pelo parâmetro da rota (/eventos/{id}/perguntas),
     * então o injetamos nos dados validados para que a regra exists funcione.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'evento_id' => $this->route('id'),
        ]);
    }

    /**
     * Mensagens de erro amigáveis.
     */
    public function messages(): array
    {
        return [
            'texto.required' => 'O texto da pergunta é obrigatório.',
            'texto.min'      => 'A pergunta precisa ter no mínimo :min caracteres.',
            'texto.max'      => 'A pergunta pode ter no máximo :max caracteres.',
            'evento_id.exists' => 'O evento informado não existe.',
        ];
    }
}
