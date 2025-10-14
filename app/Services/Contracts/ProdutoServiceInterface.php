<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Produto;

interface ProdutoServiceInterface
{
    public function listar(Request $request): LengthAwarePaginator;
    public function dadosEdicao(int $id = 0): array;
    public function salvar(array $dados): Produto;
    public function excluir(int $id): void;
}

