
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePerguntaRequest extends FormRequest
{
    /**
     * Determina se o usuário está autorizado
     * a realizar esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regras de validação das perguntas.
     */
    public function rules(): array
    {
        return [
            'texto' => 'required|string|min:10|max:255',
            'evento_id' => 'required|exists:eventos,id',
        ];
    }
}
