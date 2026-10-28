<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeracaoRequest extends FormRequest
{
    /**
     * TICKET #015: qualquer usuário autenticado pode solicitar moderação
     * (a rota já está protegida pelo middleware auth).
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['required', 'string', 'min:10', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo.required' => 'Informe o motivo da moderação.',
            'motivo.min' => 'O motivo deve ter pelo menos :min caracteres.',
            'motivo.max' => 'O motivo deve ter no máximo :max caracteres.',
        ];
    }
}
