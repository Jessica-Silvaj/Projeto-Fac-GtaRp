<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'matricula' => [
                'required', 'string', 'max:150',
                Rule::unique('usuarios', 'matricula')->ignore($this->input('id')),
            ],
            'nome' => ['required', 'string', 'max:150'],
            'situacao' => ['required', 'integer', 'exists:SITUACAO,id'],
            'perfil' => ['required', 'integer', 'exists:PERFIL,id'],
            'data_admissao' => ['required', 'date_format:d/m/Y'],
            'senha' => ['nullable', 'string', 'min:6'],
            'funcoes' => ['nullable', 'array'],
            'funcoes.*' => ['integer', 'exists:FUNCAO,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'matricula.required' => 'Informe o passaporte.',
            'nome.required' => 'Informe o nome.',
            'situacao.required' => 'Informe a situação.',
            'perfil.required' => 'Informe o perfil.',
            'data_admissao.required' => 'Informe a data de admissão.',
            'data_admissao.date_format' => 'Data de admissão deve estar no formato DD/MM/AAAA.',
            'senha.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ];
    }
}

