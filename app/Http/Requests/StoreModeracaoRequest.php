<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModeracaoRequest extends FormRequest
{
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
            'motivo.required' => 'Informe o motivo da denúncia.',
            'motivo.string' => 'O motivo deve ser um texto.',
            'motivo.min' => 'O motivo deve ter pelo menos 10 caracteres.',
            'motivo.max' => 'O motivo deve ter no máximo 255 caracteres.',
        ];
    }
}