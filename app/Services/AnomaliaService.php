<?php

namespace App\Services;

use App\Models\Baus;
use App\Models\Itens;
use App\Models\Lancamento;
use App\Models\Produto;
use App\Utils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AnomaliaService
{
    private ?array $mapaLimitesEspecificos = null;
    private ?array $mapaProdutosFabricaveis = null;

    public function dashboard(Request $request): array
    {
        $inicio = $this->normalizarData($request->get('inicio'));
        $fim = $this->normalizarData($request->get('fim'));
        $itensIdFiltro = (int) ($request->get('itens_id') ?? 0);
        $bauFiltroId = (int) ($request->get('bau_id') ?? 0);
        $usuarioFiltroId = (int) ($request->get('usuario_id') ?? 0);

        if (!$inicio || !$fim) {
            $fim = Carbon::now()->toDateString();
            $inicio = Carbon::now()->subDays(30)->toDateString();
        }

        $consolidado = $this->consolidarSaldos($inicio, $fim, $itensIdFiltro, $bauFiltroId, $usuarioFiltroId);
        $saldos = $consolidado['saldos'];
        $resumoBausCollection = $consolidado['resumo_baus'];

        $negativos = $saldos->filter(fn($row) => ($row['saldo'] ?? 0) < 0)
            ->values()
            ->map(function ($row) {
                $row['saldo'] = (int) $row['saldo'];
                return $row;
            });

        $negativosPaginator = $this->paginador($negativos, route('bau.lancamentos.anomalias'), $request, 10, 'negativos_page');

        $estoquesCriticosCollection = $this->filtrarEstoquesCriticos($resumoBausCollection);
        $estoquesCriticosPaginator = $this->paginador($estoquesCriticosCollection, route('bau.lancamentos.anomalias'), $request, 10, 'criticos_page');

        $bausCriticosCollection = $this->filtrarBausNoLimite($resumoBausCollection);
        $bausCriticosPaginator = $this->paginador($bausCriticosCollection, route('bau.lancamentos.anomalias'), $request, 10, 'baus_page');

        $movimentosAtipicosCollection = $this->movimentosAtipicos($inicio, $fim, $itensIdFiltro, $bauFiltroId, $usuarioFiltroId);
        $movimentosAtipicosPaginator = $this->paginador($movimentosAtipicosCollection, route('bau.lancamentos.anomalias'), $request, 10, 'movimentos_page');

        $itemSelecionado = null;
        if ($itensIdFiltro > 0) {
            $it = Itens::find($itensIdFiltro);
            if ($it) {
                $itemSelecionado = ['id' => $it->id, 'nome' => $it->nome];
            }
        }

        $bauSelecionado = null;
        if ($bauFiltroId > 0) {
            $bau = Baus::find($bauFiltroId);
            if ($bau) {
                $bauSelecionado = ['id' => $bau->id, 'nome' => $bau->nome];
            }
        }

        $totais = [
            'negativos' => $negativos->count(),
            'estoquesCriticos' => $estoquesCriticosCollection->count(),
            'bausCriticos' => $bausCriticosCollection->count(),
            'movimentosAtipicos' => $movimentosAtipicosCollection->count(),
        ];

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'itensIdFiltro' => $itensIdFiltro,
            'bauFiltroId' => $bauFiltroId,
            'usuarioFiltroId' => $usuarioFiltroId,
            'itemSelecionado' => $itemSelecionado,
            'bauSelecionado' => $bauSelecionado,
            'negativos' => $negativosPaginator,
            'estoquesCriticos' => $estoquesCriticosPaginator,
            'bausCriticos' => $bausCriticosPaginator,
            'movimentosAtipicos' => $movimentosAtipicosPaginator,
            'totais' => $totais,
        ];
    }

    public function navbarAlertas(): array
    {
        $consolidado = $this->consolidarSaldos(
            Carbon::now()->subDays(180)->toDateString(),
            Carbon::now()->toDateString(),
            0,
            0,
            0
        );

        $negativos = $consolidado['saldos']->filter(fn($row) => ($row['saldo'] ?? 0) < 0);
        $estoquesCriticos = $this->filtrarEstoquesCriticos($consolidado['resumo_baus']);
        $bausCriticos = $this->filtrarBausNoLimite($consolidado['resumo_baus']);
        $movimentos = $this->movimentosAtipicos(
            Carbon::now()->subDays((int) config('anomalias.janela_movimento_dias', 7))->toDateString(),
            Carbon::now()->toDateString(),
            0,
            0,
            0
        );

        $items = collect()
            ->merge($negativos->map(fn($row) => [
                'tipo' => 'Negativo',
                'item' => $row['item_nome'],
                'bau' => $row['bau_nome'],
                'descricao' => 'Saldo: ' . (int) $row['saldo'],
            ]))
            ->merge($estoquesCriticos->map(fn($row) => [
                'tipo' => 'Critico',
                'item' => $row['item_nome'],
                'bau' => $row['bau_nome'],
                'descricao' => 'Saldo: ' . (int) ($row['quantidade'] ?? 0) . ' / Limite: ' . ($row['limite'] ?? 0),
            ]))
            ->merge($bausCriticos->map(fn($row) => [
                'tipo' => 'Bau limite',
                'item' => $row['bau_nome'],
                'bau' => 'Ocupacao',
                'descricao' => number_format(($row['ocupacao_percentual'] ?? 0) * 100, 1, ',', '.') . '%',
            ]))
            ->merge($movimentos->map(fn($row) => [
                'tipo' => 'Movimento atipico',
                'item' => $row['item'],
                'bau' => trim(($row['bau_origem'] ? $row['bau_origem'] . ' -> ' : '') . ($row['bau_destino'] ?? '')),
                'descricao' => 'Qtd: ' . (int) $row['quantidade'],
            ]))
            ->take(5)
            ->values();

        return [
            'count' => $items->count(),
            'items' => $items->all(),
        ];
    }
    private function consolidarSaldos(string $inicio, string $fim, int $itensIdFiltro, int $bauFiltroId, int $usuarioFiltroId): array
    {
        $bauFiltroAny = $bauFiltroId;

        $aplicarFiltros = function ($query) use ($inicio, $fim, $itensIdFiltro, $usuarioFiltroId, $bauFiltroAny) {
            return $query
                ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
                ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
                ->when($inicio, fn($q) => $q->whereDate('data_atribuicao', '>=', $inicio))
                ->when($fim, fn($q) => $q->whereDate('data_atribuicao', '<=', $fim))
                ->when($bauFiltroAny > 0, function ($q) use ($bauFiltroAny) {
                    $q->where(function ($sub) use ($bauFiltroAny) {
                        $sub->where('bau_destino_id', $bauFiltroAny)
                            ->orWhere('bau_origem_id', $bauFiltroAny);
                    });
                });
        };

        $movimentosEntrada = $aplicarFiltros(
            Lancamento::query()
                ->select([
                    'itens_id',
                    DB::raw('bau_destino_id as bau_id'),
                    DB::raw('quantidade as movimento'),
                ])
                ->whereIn('tipo', ['ENTRADA', 'TRANSFERENCIA'])
                ->whereNotNull('bau_destino_id')
        );

        $movimentosSaida = $aplicarFiltros(
            Lancamento::query()
                ->select([
                    'itens_id',
                    DB::raw('bau_origem_id as bau_id'),
                    DB::raw('-quantidade as movimento'),
                ])
                ->whereIn('tipo', ['SAIDA', 'TRANSFERENCIA'])
                ->whereNotNull('bau_origem_id')
        );

        $movimentosUnion = $movimentosEntrada->unionAll($movimentosSaida);

        $saldosRaw = DB::query()
            ->fromSub($movimentosUnion, 'movimentos')
            ->select('itens_id', 'bau_id', DB::raw('SUM(movimento) as saldo'))
            ->whereNotNull('bau_id')
            ->groupBy('itens_id', 'bau_id')
            ->orderBy('itens_id')
            ->get();

        $ajustesFabricacao = $this->calcularAjustesFabricacao($inicio, $fim, $bauFiltroAny, $usuarioFiltroId);
        if (!empty($ajustesFabricacao)) {
            $saldosRaw = $this->aplicarAjustesFabricacao($saldosRaw, $ajustesFabricacao);
        }

        $itensIds = $saldosRaw->pluck('itens_id')->filter()->unique()->values();
        $bauIds = $saldosRaw->pluck('bau_id')->filter()->unique()->values();

        $itensMap = $itensIds->isEmpty()
            ? collect()
            : Itens::whereIn('id', $itensIds)->get()->keyBy('id');
        $bausMap = $bauIds->isEmpty()
            ? collect()
            : Baus::whereIn('id', $bauIds)->get()->keyBy('id');

        $saldos = $saldosRaw->map(function ($row) use ($itensMap, $bausMap) {
            $item = $itensMap->get($row->itens_id);
            $bau = $bausMap->get($row->bau_id);
            return [
                'itens_id' => (int) $row->itens_id,
                'item_nome' => $item->nome ?? ('Item #' . $row->itens_id),
                'bau_id' => (int) $row->bau_id,
                'bau_nome' => $bau->nome ?? ('BaÃƒÆ’Ã‚Âº #' . $row->bau_id),
                'saldo' => (int) $row->saldo,
            ];
        })->values();

        $resumoBaus = $saldos->groupBy('bau_id')->map(function (Collection $grupo) {
            return [
                'bau_id' => $grupo->first()['bau_id'],
                'bau_nome' => $grupo->first()['bau_nome'],
                'quantidade' => $grupo->where('saldo', '>', 0)->sum('saldo'),
                'itens' => $grupo->where('saldo', '>', 0)->count(),
                'itens_lista' => $grupo->where('saldo', '>', 0)->map(fn($row) => [
                    'itens_id' => $row['itens_id'],
                    'item_nome' => $row['item_nome'],
                    'quantidade' => (int) $row['saldo'],
                ])->sortByDesc('quantidade')->values()->all(),
            ];
        })->values();

        return [
            'saldos' => $saldos,
            'resumo_baus' => $resumoBaus,
        ];
    }

    private function getMapaProdutosFabricaveis(): array
    {
        if ($this->mapaProdutosFabricaveis !== null) {
            return $this->mapaProdutosFabricaveis;
        }

        $itensFabricados = Itens::query()
            ->whereIn('id', [51, 58])
            ->get(['id', 'nome'])
            ->mapWithKeys(function ($item) {
                $nome = Str::upper(trim((string) $item->nome));
                return [$nome => (int) $item->id];
            });

        if ($itensFabricados->isEmpty()) {
            return $this->mapaProdutosFabricaveis = [];
        }

        $produtos = Produto::query()
            ->where('ativo', 1)
            ->with(['itens' => fn($q) => $q->withPivot('quantidade')])
            ->get();

        $mapa = [];
        foreach ($produtos as $produto) {
            $nomeProduto = Str::upper(trim((string) $produto->nome));
            $itemId = $itensFabricados->get($nomeProduto);
            if (!$itemId) {
                continue;
            }

            $componentes = [];
            foreach ($produto->itens as $componente) {
                $quantidade = (int) ($componente->pivot->quantidade ?? 0);
                if ($quantidade <= 0) {
                    continue;
                }
                $componentes[(int) $componente->id] = $quantidade;
            }

            if (empty($componentes)) {
                continue;
            }

            $mapa[(int) $itemId] = [
                'produto_id' => (int) $produto->id,
                'saida' => max(1, (int) ($produto->quantidade ?? 1)),
                'componentes' => $componentes,
            ];
        }

        return $this->mapaProdutosFabricaveis = $mapa;
    }

    private function calcularAjustesFabricacao(string $inicio, string $fim, int $bauFiltroAny, int $usuarioFiltroId): array
    {
        $mapaProdutos = $this->getMapaProdutosFabricaveis();
        if (empty($mapaProdutos)) {
            return [];
        }

        $tiposPermitidos = ['ENTRADA', 'SAIDA', 'TRANSFERENCIA'];

        $query = Lancamento::query()
            ->whereIn('itens_id', array_map('intval', array_keys($mapaProdutos)))
            ->whereIn('tipo', $tiposPermitidos)
            ->where('fabricacao', 1);

        if ($usuarioFiltroId > 0) {
            $query->where('usuario_id', $usuarioFiltroId);
        }

        if (!empty($inicio)) {
            $query->whereDate('data_atribuicao', '>=', $inicio);
        }

        if (!empty($fim)) {
            $query->whereDate('data_atribuicao', '<=', $fim);
        }

        if ($bauFiltroAny > 0) {
            $query->where(function ($sub) use ($bauFiltroAny) {
                $sub->where('bau_destino_id', $bauFiltroAny)
                    ->orWhere('bau_origem_id', $bauFiltroAny);
            });
        }

        $lancamentos = $query->get(['itens_id', 'quantidade', 'bau_destino_id', 'bau_origem_id', 'tipo']);

        $ajustes = [];
        foreach ($lancamentos as $lanc) {
            $infoProduto = $mapaProdutos[(int) $lanc->itens_id] ?? null;
            if (!$infoProduto) {
                continue;
            }

            $quantidadeBatch = max(1, (int) ($infoProduto['saida'] ?? 1));
            $quantidadeLancamento = (int) ($lanc->quantidade ?? 0);
            if ($quantidadeLancamento <= 0) {
                continue;
            }

            $fator = $quantidadeLancamento / $quantidadeBatch;
            if ($fator <= 0) {
                continue;
            }

            $tipoLanc = strtoupper((string) $lanc->tipo);
            $localConsumo = 0;
            if ($tipoLanc === 'ENTRADA') {
                $localConsumo = (int) ($lanc->bau_destino_id ?? 0);
            } elseif ($tipoLanc === 'SAIDA') {
                $localConsumo = (int) ($lanc->bau_origem_id ?? 0);
            } elseif ($tipoLanc === 'TRANSFERENCIA') {
                $localConsumo = (int) ($lanc->bau_origem_id ?? 0);
                if ($localConsumo <= 0) {
                    $localConsumo = (int) ($lanc->bau_destino_id ?? 0);
                }
            }

            if ($localConsumo <= 0) {
                continue;
            }

            foreach ($infoProduto['componentes'] as $componenteId => $qtdComponente) {
                $consumo = (int) ceil($qtdComponente * $fator);
                if ($consumo <= 0) {
                    continue;
                }

                $ajustes[] = [
                    'itens_id' => (int) $componenteId,
                    'bau_id' => $localConsumo,
                    'saldo' => -$consumo,
                ];
            }
        }

        return $ajustes;
    }

    private function aplicarAjustesFabricacao(Collection $saldosRaw, array $ajustes): Collection
    {
        if (empty($ajustes)) {
            return $saldosRaw;
        }

        $map = [];
        foreach ($saldosRaw as $row) {
            $key = $row->itens_id . ':' . $row->bau_id;
            $map[$key] = $row;
        }

        foreach ($ajustes as $ajuste) {
            $key = $ajuste['itens_id'] . ':' . $ajuste['bau_id'];
            if (!isset($map[$key])) {
                $map[$key] = (object) [
                    'itens_id' => (int) $ajuste['itens_id'],
                    'bau_id' => (int) $ajuste['bau_id'],
                    'saldo' => 0,
                ];
            }

            $map[$key]->saldo += (int) $ajuste['saldo'];
        }

        return collect(array_values($map));
    }

    private function filtrarEstoquesCriticos(Collection $resumoBaus): Collection
    {
        $limiteCriticoPadrao = (float) config('anomalias.limite_estoque_critico', 10);
        $mapaEspecifico = $this->getMapaLimitesEspecificos();

        return $resumoBaus->flatMap(function (array $bau) use ($limiteCriticoPadrao, $mapaEspecifico) {
            $bauId = (int) ($bau['bau_id'] ?? 0);
            return collect($bau['itens_lista'] ?? [])
                ->map(function ($item) use ($bauId, $limiteCriticoPadrao, $mapaEspecifico, $bau) {
                    $itemId = (int) ($item['itens_id'] ?? 0);
                    $quantidade = (int) ($item['quantidade'] ?? 0);
                    $key = $bauId . ':' . $itemId;
                    $limite = $mapaEspecifico[$key] ?? $limiteCriticoPadrao;
                    return [
                        'itens_id' => $itemId,
                        'item_nome' => $item['item_nome'] ?? ('Item #' . $itemId),
                        'quantidade' => $quantidade,
                        'bau_id' => $bauId,
                        'bau_nome' => $bau['bau_nome'] ?? ('Bau #' . $bauId),
                        'limite' => $limite,
                    ];
                })
                ->filter(function ($row) {
                    return ($row['quantidade'] ?? 0) > 0 && ($row['quantidade'] ?? 0) <= ($row['limite'] ?? 0);
                });
        })->values();
    }
    private function filtrarBausNoLimite(Collection $resumoBaus): Collection
    {
        $percentual = (float) config('anomalias.limite_percentual_bau', 0.8);
        $limitePadrao = (int) config('anomalias.limite_padrao_bau', 1000);
        $mapaLimites = collect(config('anomalias.limites_baus', []));

        return $resumoBaus->map(function (array $bau) use ($percentual, $limitePadrao, $mapaLimites) {
            $limite = $mapaLimites->get((string) ($bau['bau_id'] ?? ''))
                ?? $mapaLimites->get($bau['bau_nome'] ?? '')
                ?? $limitePadrao;

            $limite = max(1, (int) $limite);
            $ocupacao = (int) ($bau['quantidade'] ?? 0);
            $percent = $ocupacao / $limite;
            $bau['limite'] = $limite;
            $bau['ocupacao_percentual'] = $percent;
            return $bau;
        })->filter(fn($bau) => ($bau['quantidade'] ?? 0) > 0 && ($bau['ocupacao_percentual'] ?? 0) >= $percentual)
            ->sortByDesc('ocupacao_percentual')
            ->values();
    }

    private function movimentosAtipicos(string $inicio, string $fim, int $itensIdFiltro, int $bauFiltroId, int $usuarioFiltroId): Collection
    {
        $limiteQtd = (int) config('anomalias.limite_quantidade_movimento', 500);
        $janelaDias = (int) config('anomalias.janela_movimento_dias', 7);
        $inicioJanela = Carbon::parse($fim)->subDays($janelaDias)->toDateString();

        return Lancamento::query()
            ->with(['item', 'bauOrigem', 'bauDestino', 'usuario'])
            ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
            ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
            ->when($bauFiltroId > 0, function ($q) use ($bauFiltroId) {
                $q->where(function ($sub) use ($bauFiltroId) {
                    $sub->where('bau_origem_id', $bauFiltroId)->orWhere('bau_destino_id', $bauFiltroId);
                });
            })
            ->whereBetween('data_atribuicao', [$inicioJanela, $fim])
            ->where('quantidade', '>=', $limiteQtd)
            ->orderByDesc('data_atribuicao')
            ->get()
            ->map(function ($lanc) {
                return [
                    'id' => $lanc->id,
                    'data' => $lanc->data_atribuicao ? Carbon::parse($lanc->data_atribuicao)->format('d/m/Y H:i') : '',
                    'item' => optional($lanc->item)->nome,
                    'quantidade' => (int) $lanc->quantidade,
                    'tipo' => (string) $lanc->tipo,
                    'bau_origem' => optional($lanc->bauOrigem)->nome,
                    'bau_destino' => optional($lanc->bauDestino)->nome,
                    'usuario' => optional($lanc->usuario)->nome,
                ];
            });
    }
    private function getMapaLimitesEspecificos(): array
    {
        if ($this->mapaLimitesEspecificos !== null) {
            return $this->mapaLimitesEspecificos;
        }

        $this->mapaLimitesEspecificos = collect(config('anomalias.limites_especificos', []))
            ->filter(function ($row) {
                return !empty($row['bau_id']) && !empty($row['item_id']) && isset($row['limite']);
            })
            ->mapWithKeys(function ($row) {
                $bauId = (int) $row['bau_id'];
                $itemId = (int) $row['item_id'];
                return [$bauId . ':' . $itemId => (float) $row['limite']];
            })
            ->all();

        return $this->mapaLimitesEspecificos;
    }
    private function paginador(Collection $collection, string $route, Request $request, int $perPage, string $pageName)
    {
        return Utils::arrayPaginator($collection->all(), $route, $request, $perPage, $pageName);
    }

    private function normalizarData(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }
        $valor = trim($valor);
        if ($valor === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $valor)->format('Y-m-d');
        } catch (\Throwable $e) {
        }

        try {
            return Carbon::parse($valor)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
