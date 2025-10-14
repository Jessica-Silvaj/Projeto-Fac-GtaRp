<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Permissoes;

interface PermissoesServiceInterface
{
    /**
     * Lista permissões com filtros e paginação.
     */
    public function listar(Request $request): LengthAwarePaginator;

    /**
     * Retorna dados necessários para a tela de edição/criação.
     * Deve incluir: permissões, funções disponíveis e selecionadas.
     */
    public function dadosEdicao(Request $request, int $id = 0): array;

    /**
     * Cria ou atualiza uma permissão e sincroniza relações.
     */
    public function salvar(array $dados): Permissoes;

    /**
     * Exclui uma permissão e limpa relações.
     */
    public function excluir(int $id): void;
    public function filtrosIndex(): array;
}
