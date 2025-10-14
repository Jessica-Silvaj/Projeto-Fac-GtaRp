<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PermissaoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'nome' => ['required', 'string', 'max:150'],
            'descricao' => ['nullable', 'string', 'max:255'],
            'ativo' => ['required', 'boolean'],
            'funcoes' => ['nullable', 'array'],
            'funcoes.*' => ['integer', 'exists:FUNCAO,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome.',
            'nome.max' => 'O nome pode ter no máximo 150 caracteres.',
            'descricao.max' => 'A descrição pode ter no máximo 255 caracteres.',
            'ativo.required' => 'Informe se está ativo.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
            'funcoes.array' => 'Funções deve ser uma lista.',
            'funcoes.*.integer' => 'Função inválida.',
            'funcoes.*.exists' => 'Função inválida.',
        ];
    }
}

