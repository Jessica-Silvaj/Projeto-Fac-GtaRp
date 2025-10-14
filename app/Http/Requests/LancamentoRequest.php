<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LancamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'id' => ['nullable','integer'],
            'itens_id' => ['required','integer','exists:ITENS,id'],
            'tipo' => ['required','in:ENTRADA,SAIDA,TRANSFERENCIA'],
            'quantidade' => ['required','integer','min:1'],
            'observacao' => ['nullable','string','max:255'],
            'bau_origem_id' => ['nullable','integer','exists:BAUS,id'],
            'bau_destino_id' => ['nullable','integer','exists:BAUS,id'],
        ];

        // Validações condicionais simples
        if ($this->input('tipo') === 'ENTRADA') {
            $rules['bau_destino_id'] = ['required','integer','exists:BAUS,id'];
        } elseif ($this->input('tipo') === 'SAIDA') {
            $rules['bau_origem_id'] = ['required','integer','exists:BAUS,id'];
        } elseif ($this->input('tipo') === 'TRANSFERENCIA') {
            $rules['bau_origem_id'] = ['required','integer','exists:BAUS,id','different:bau_destino_id'];
            $rules['bau_destino_id'] = ['required','integer','exists:BAUS,id','different:bau_origem_id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'itens_id.required' => 'Selecione o item.',
            'tipo.required' => 'Informe o tipo de lançamento.',
            'quantidade.required' => 'Informe a quantidade.',
            'quantidade.min' => 'A quantidade deve ser no mínimo 1.',
            'bau_origem_id.required' => 'Informe o baú de origem.',
            'bau_destino_id.required' => 'Informe o baú de destino.',
            'bau_origem_id.different' => 'Baú de origem e destino devem ser diferentes.',
            'bau_destino_id.different' => 'Baú de origem e destino devem ser diferentes.',
        ];
    }
}
