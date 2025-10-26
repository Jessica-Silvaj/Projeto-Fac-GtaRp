<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\FilaEspera;

class FilaVendaProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('desconto_aplicado')) {
            $this->merge(['desconto_aplicado' => 'nao']);
        }

        $this->merge([
            'dinheiro_limpo' => $this->normalizeCurrency($this->input('dinheiro_limpo')),
            'dinheiro_sujo' => $this->normalizeCurrency($this->input('dinheiro_sujo')),
            'desconto_valor' => $this->normalizeCurrency($this->input('desconto_valor')),
        ]);
    }

    public function rules(): array
    {
        $isCancelado = $this->input('status') === FilaEspera::STATUS_CANCELADO;

        $rules = [
            'responsavel' => ['nullable', 'integer', 'exists:USUARIOS,id'],
            'status' => ['required', Rule::in([
                FilaEspera::STATUS_EM_ATENDIMENTO,
                FilaEspera::STATUS_CONCLUIDO,
                FilaEspera::STATUS_CANCELADO,
            ])],
            'tabela_preco_global' => ['required', Rule::in(['padrao', 'desconto', 'alianca'])],
            'observacao' => ['nullable', 'string', 'max:500'],
            'dinheiro_limpo' => ['nullable', 'numeric', 'min:0'],
            'dinheiro_sujo' => ['nullable', 'numeric', 'min:0'],
            'desconto_aplicado' => ['required', Rule::in(['sim', 'nao'])],
            'desconto_valor' => ['nullable', 'numeric', 'required_if:desconto_aplicado,sim'],
            'desconto_motivo' => ['nullable', 'string', 'max:255', 'required_if:desconto_aplicado,sim'],
            'pagamento_tipo' => ['required', Rule::in(['limpo', 'sujo', 'ambos'])],
        ];

        // Se não for cancelado, exigir produtos e quantidades
        if (!$isCancelado) {
            $rules['produto_id'] = ['required', 'array', 'min:1'];
            $rules['produto_id.*'] = ['required', 'integer', 'exists:PRODUTO,id'];
            $rules['quantidade'] = ['required', 'array', 'min:1'];
            $rules['quantidade.*'] = ['required', 'integer', 'min:1'];
        } else {
            // Se cancelado, tornar produtos opcionais
            $rules['produto_id'] = ['nullable', 'array'];
            $rules['produto_id.*'] = ['nullable', 'integer', 'exists:PRODUTO,id'];
            $rules['quantidade'] = ['nullable', 'array'];
            $rules['quantidade.*'] = ['nullable', 'integer', 'min:1'];
        }

        $rules['item_observacao'] = ['nullable', 'array'];
        $rules['item_observacao.*'] = ['nullable', 'string', 'max:255'];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'responsavel' => 'responsavel',
            'status' => 'status',
            'tabela_preco_global' => 'tabela de preco',
            'dinheiro_limpo' => 'dinheiro limpo',
            'dinheiro_sujo' => 'dinheiro sujo',
            'desconto_aplicado' => 'desconto aplicado',
            'desconto_valor' => 'valor do desconto',
            'desconto_motivo' => 'motivo do desconto',
            'pagamento_tipo' => 'tipo de pagamento',
            'produto_id.*' => 'produto',
            'quantidade.*' => 'quantidade',
            'item_observacao.*' => 'observacao do item',
        ];
    }

    protected function normalizeCurrency($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $sanitized = str_replace(['R$', 'r$', ' ', "\u{00A0}", "\u{202F}"], '', $value);

        if (str_contains($sanitized, ',')) {
            $sanitized = str_replace('.', '', $sanitized);
            $sanitized = str_replace(',', '.', $sanitized);
        } else {
            $dotCount = substr_count($sanitized, '.');
            if ($dotCount > 0) {
                $lastDot = strrpos($sanitized, '.');
                $decimais = strlen($sanitized) - $lastDot - 1;
                if ($dotCount > 1 || $decimais > 2) {
                    $sanitized = str_replace('.', '', $sanitized);
                }
            }
        }

        return is_numeric($sanitized) ? (float) $sanitized : null;
    }
}
