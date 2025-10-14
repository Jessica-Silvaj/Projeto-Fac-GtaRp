<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PerfilSelfRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer'],
            'nome' => ['required', 'string', 'max:150'],
            'matricula' => ['required', 'string', 'max:150'],
            'data_admissao' => ['required', 'date_format:d/m/Y'],
            'situacao_id' => ['required', 'integer'],
            'perfil_id' => ['required', 'integer'],
            'senha' => ['nullable', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome.',
            'matricula.required' => 'Informe o passaporte.',
            'data_admissao.required' => 'Informe a data de admissão.',
            'data_admissao.date_format' => 'Data de admissão deve estar no formato DD/MM/AAAA.',
            'situacao_id.required' => 'Informe a situação.',
            'perfil_id.required' => 'Informe o perfil.',
            'senha.min' => 'A senha deve ter no mínimo 6 caracteres.',
        ];
    }
}


