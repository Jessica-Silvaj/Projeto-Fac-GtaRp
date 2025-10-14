<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Perfil;

interface PerfilAdmServiceInterface
{
    public function listar(Request $request): LengthAwarePaginator;
    public function dadosEdicao(int $id = 0): array;
    public function salvar(array $dados): Perfil;
    public function excluir(int $id): void;
}

