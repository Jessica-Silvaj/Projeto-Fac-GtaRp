<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;

interface OrganizacaoServiceInterface
{
    public function listar(Request $request);
    public function dadosEdicao(int $id): array;
    public function salvar(array $data): void;
    public function excluir(int $id): void;
}
