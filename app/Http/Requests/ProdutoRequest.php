<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProdutoRequest extends FormRequest
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
            'quantidade' => ['required', 'integer', 'min:0'],
            'ativo' => ['required', 'boolean'],
            'sync_itens' => ['sometimes', 'boolean'],

            // composição (materiais do produto)
            'itens' => ['nullable', 'array'],
            'itens.*.id' => ['nullable', 'integer', 'exists:ITENS,id', 'distinct'],
            'itens.*.quantidade' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'Informe o nome.',
            'nome.max' => 'O nome pode ter no máximo 150 caracteres.',
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.integer' => 'A quantidade deve ser numérica.',
            'quantidade.min' => 'A quantidade deve ser zero ou maior.',
            'ativo.required' => 'Informe se está ativo.',
            'ativo.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',

            'itens.array' => 'Itens inválidos.',
            'itens.*.id.exists' => 'Item inválido.',
            'itens.*.id.distinct' => 'Há itens duplicados na composição.',
            'itens.*.quantidade.required' => 'Informe a quantidade do item.',
            'itens.*.quantidade.integer' => 'Quantidade do item deve ser numérica.',
            'itens.*.quantidade.min' => 'Quantidade do item deve ser pelo menos 1.',
        ];
    }
}
