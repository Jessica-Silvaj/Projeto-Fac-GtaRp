<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\FilaEspera;
use Carbon\Carbon;

class FilaEsperaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $camposDatas = ['data_pedido', 'data_entrega_estimada'];

        foreach ($camposDatas as $campo) {
            $valor = $this->input($campo);
            if (!empty($valor)) {
                try {
                    $data = Carbon::createFromFormat('d/m/Y', $valor);
                    $this->merge([
                        $campo => $data->format('Y-m-d'),
                    ]);
                } catch (\Throwable $e) {
                    // mantém valor original; a validação acusará o erro
                }
            }
        }
    }

    public function rules(): array
    {
        return [
            'organizacao_id' => ['nullable', 'integer', 'exists:ORGANIZACAO,id'],
            'nome' => ['required', 'string', 'max:255'],
            'data_pedido' => ['required', 'date'],
            'data_entrega_estimada' => ['nullable', 'date', 'after_or_equal:data_pedido'],
            'usuario_id' => ['nullable', 'integer', 'exists:USUARIOS,id'],
            'pedido' => ['required', 'string'],
            'status' => ['required', Rule::in([
                FilaEspera::STATUS_PENDENTE,
                FilaEspera::STATUS_EM_ATENDIMENTO,
                FilaEspera::STATUS_CONCLUIDO,
                FilaEspera::STATUS_CANCELADO,
            ])],
            'dinheiro_limpo' => ['nullable', 'numeric', 'min:0'],
            'dinheiro_sujo' => ['nullable', 'numeric', 'min:0'],
            'desconto_aplicado' => ['nullable', 'boolean'],
            'desconto_valor' => ['nullable', 'numeric', 'min:0'],
            'desconto_motivo' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nome' => 'nome do cliente',
            'organizacao_id' => 'organização',
            'usuario_id' => 'responsável',
            'pedido' => 'descrição do pedido',
            'data_pedido' => 'data do pedido',
            'data_entrega_estimada' => 'data de entrega estimada',
            'status' => 'status do pedido',
            'dinheiro_limpo' => 'valor em dinheiro limpo',
            'dinheiro_sujo' => 'valor em dinheiro sujo',
            'desconto_aplicado' => 'desconto aplicado',
            'desconto_valor' => 'valor do desconto',
            'desconto_motivo' => 'motivo do desconto',
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do cliente é obrigatório.',
            'nome.max' => 'O nome do cliente não pode ter mais de 255 caracteres.',
            'data_pedido.required' => 'A data do pedido é obrigatória.',
            'data_pedido.date' => 'A data do pedido deve ser uma data válida.',
            'data_entrega_estimada.date' => 'A data de entrega estimada deve ser uma data válida.',
            'data_entrega_estimada.after_or_equal' => 'A data de entrega estimada deve ser posterior ou igual à data do pedido.',
            'pedido.required' => 'A descrição do pedido é obrigatória.',
            'status.required' => 'O status do pedido é obrigatório.',
            'status.in' => 'O status do pedido selecionado é inválido.',
            'organizacao_id.exists' => 'A organização selecionada não existe.',
            'usuario_id.exists' => 'O responsável selecionado não existe.',
            'dinheiro_limpo.numeric' => 'O valor em dinheiro limpo deve ser um número.',
            'dinheiro_limpo.min' => 'O valor em dinheiro limpo não pode ser negativo.',
            'dinheiro_sujo.numeric' => 'O valor em dinheiro sujo deve ser um número.',
            'dinheiro_sujo.min' => 'O valor em dinheiro sujo não pode ser negativo.',
            'desconto_valor.numeric' => 'O valor do desconto deve ser um número.',
            'desconto_valor.min' => 'O valor do desconto não pode ser negativo.',
            'desconto_motivo.max' => 'O motivo do desconto não pode ter mais de 255 caracteres.',
        ];
    }
}
