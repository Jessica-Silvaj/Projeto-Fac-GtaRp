<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Lancamento;

interface LancamentoServiceInterface
{
    public function listar(Request $request): LengthAwarePaginator;
    public function dadosEdicao(int $id = 0): array;
    public function salvar(array $dados): Lancamento;
    public function excluir(int $id): void;
    public function historico(Request $request): array;
}
