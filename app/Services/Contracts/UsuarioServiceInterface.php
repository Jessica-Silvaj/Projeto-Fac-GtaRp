<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Usuario;

interface UsuarioServiceInterface
{
    public function listar(Request $request): LengthAwarePaginator;
    public function dadosEdicao(Request $request, int $id = 0): array;
    public function salvar(array $dados): Usuario;
    public function excluir(int $id): void;
}

