<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\FilaEspera;

interface FilaEsperaServiceInterface
{
    public function listar(Request $request): LengthAwarePaginator;

    public function resumoStatus(Request $request): array;

    public function responsaveis(): Collection;

    public function dadosEdicao(int $id): array;

    public function salvar(int $id, array $dados): FilaEspera;

    public function dadosVenda(int $id): array;

    public function registrarVenda(FilaEspera $fila, array $dados): FilaEspera;

    public function dadosCriacao(): array;

    public function criar(array $dados): FilaEspera;

    public function excluir(int $id): void;

    public function historicoVendas(Request $request): array;

    public function seriesDiarias(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array;

    public function seriesSemanais(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array;

    public function seriesMensais(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array;

    public function rankingHistorico(string $tipo, Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array;
}
