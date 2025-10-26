<?php

namespace App\Services;

use App\Models\FilaEspera;
use App\Models\FilaEsperaItem;
use App\Models\Organizacao;
use App\Models\Produto;
use App\Models\Lancamento;
use App\Models\Usuario;
use App\Services\Contracts\FilaEsperaServiceInterface;
use App\Services\Contracts\LancamentoServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FilaEsperaService implements FilaEsperaServiceInterface
{
    public function __construct(private LancamentoServiceInterface $lancamentoService) {}

    public function listar(Request $request): LengthAwarePaginator
    {
        // Paginação nativa do banco (mais eficiente para grandes volumes)
        return FilaEspera::queryComFiltros($request)
            ->with(['itens.produto'])
            ->paginate(
                perPage: 10,
                pageName: 'page'
            )
            ->withQueryString();
    }

    public function resumoStatus(Request $request): array
    {
        $query = FilaEspera::queryComFiltros($request, true)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status');

        $totais = $query->pluck('total', 'status')->all();

        foreach ([FilaEspera::STATUS_PENDENTE, FilaEspera::STATUS_EM_ATENDIMENTO, FilaEspera::STATUS_CONCLUIDO, FilaEspera::STATUS_CANCELADO] as $status) {
            $totais[$status] = $totais[$status] ?? 0;
        }

        return $totais;
    }

    public function responsaveis(): Collection
    {
        return Usuario::query()
            ->orderBy('nome')
            ->whereHas('funcoes', function ($q) {
                $q->whereRaw('UPPER(nome) LIKE ?', ['%VENDAS%']);
            })
            ->get(['id', 'nome']);
    }

    public function historicoVendas(Request $request): array
    {
        [$inicio, $fim] = $this->resolverPeriodo($request);

        $baseQuery = FilaEspera::query()
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        $totais = (clone $baseQuery)
            ->selectRaw('COUNT(*) as total_vendas, SUM(dinheiro_limpo) as total_limpo, SUM(dinheiro_sujo) as total_sujo')
            ->first();

        $totalVendas = (int) ($totais?->total_vendas ?? 0);
        $totalLimpo = (float) ($totais?->total_limpo ?? 0);
        $totalSujo = (float) ($totais?->total_sujo ?? 0);
        $faturamentoTotal = $totalLimpo + $totalSujo;
        $ticketMedio = $totalVendas > 0 ? $faturamentoTotal / max(1, $totalVendas) : 0;

        $rankOrganizacoes = $this->rankingHistorico('organizacoes', $request, 8)['data'];
        $rankSolicitantes = $this->rankingHistorico('solicitantes', $request, 8)['data'];
        $rankResponsaveis = $this->rankingHistorico('responsaveis', $request, 8)['data'];

        return [
            'periodo' => [
                'inicio' => $inicio,
                'fim' => $fim,
            ],
            'totais' => [
                'total_vendas' => $totalVendas,
                'total_limpo' => $totalLimpo,
                'total_sujo' => $totalSujo,
                'faturamento_total' => $faturamentoTotal,
                'ticket_medio' => $ticketMedio,
            ],
            'series' => [
                'diaria' => [],
                'semanal' => [],
                'mensal' => [],
            ],
            'rankings' => [
                'organizacoes' => collect($rankOrganizacoes),
                'solicitantes' => collect($rankSolicitantes),
                'responsaveis' => collect($rankResponsaveis),
            ],
        ];
    }
    public function seriesDiarias(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array
    {
        [$inicio, $fim] = $this->resolverPeriodo($request);

        $perPage = $perPageOverride ?? max(1, min(120, (int) $request->input('per_page', 30)));
        $page = $pageOverride ?? max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $baseQuery = FilaEspera::query()
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        $totalRegistros = (clone $baseQuery)
            ->selectRaw("COUNT(DISTINCT DATE(data_pedido)) as total")
            ->value('total') ?? 0;

        $series = (clone $baseQuery)
            ->selectRaw("DATE(data_pedido) as referencia_data, COUNT(*) as total_vendas, SUM(dinheiro_limpo) as total_limpo, SUM(dinheiro_sujo) as total_sujo, SUM(dinheiro_limpo + dinheiro_sujo) as faturamento")
            ->groupBy('referencia_data')
            ->orderByDesc('referencia_data')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function ($linha) {
                $data = Carbon::parse($linha->referencia_data);
                return [
                    'referencia' => $data->format('d/m/Y'),
                    'referencia_iso' => $data->format('Y-m-d'),
                    'total_vendas' => (int) $linha->total_vendas,
                    'total_limpo' => (float) ($linha->total_limpo ?? 0),
                    'total_sujo' => (float) ($linha->total_sujo ?? 0),
                    'faturamento' => (float) ($linha->faturamento ?? 0),
                ];
            })
            ->values();

        return [
            'data' => $series,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $totalRegistros,
                'last_page' => max(1, (int) ceil($totalRegistros / max(1, $perPage))),
            ],
        ];
    }

    public function seriesSemanais(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array
    {
        [$inicio, $fim] = $this->resolverPeriodo($request);

        $perPage = $perPageOverride ?? max(1, min(52, (int) $request->input('per_page', 12)));
        $page = $pageOverride ?? max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $baseQuery = FilaEspera::query()
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        $totalRegistros = (clone $baseQuery)
            ->selectRaw("COUNT(DISTINCT YEARWEEK(data_pedido, 1)) as total")
            ->value('total') ?? 0;

        $series = (clone $baseQuery)
            ->selectRaw("YEARWEEK(data_pedido, 1) as chave, MIN(data_pedido) as inicio_semana, MAX(data_pedido) as fim_semana, COUNT(*) as total_vendas, SUM(dinheiro_limpo) as total_limpo, SUM(dinheiro_sujo) as total_sujo, SUM(dinheiro_limpo + dinheiro_sujo) as faturamento")
            ->groupBy('chave')
            ->orderByDesc('chave')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function ($linha) {
                $inicio = Carbon::parse($linha->inicio_semana);
                $fim = Carbon::parse($linha->fim_semana);
                return [
                    'referencia' => $inicio->format('d/m') . ' - ' . $fim->format('d/m'),
                    'inicio_iso' => $inicio->format('Y-m-d'),
                    'fim_iso' => $fim->format('Y-m-d'),
                    'total_vendas' => (int) $linha->total_vendas,
                    'total_limpo' => (float) ($linha->total_limpo ?? 0),
                    'total_sujo' => (float) ($linha->total_sujo ?? 0),
                    'faturamento' => (float) ($linha->faturamento ?? 0),
                ];
            })
            ->values();

        return [
            'data' => $series,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $totalRegistros,
                'last_page' => max(1, (int) ceil($totalRegistros / max(1, $perPage))),
            ],
        ];
    }

    public function seriesMensais(Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array
    {
        [$inicio, $fim] = $this->resolverPeriodo($request);

        $perPage = $perPageOverride ?? max(1, min(24, (int) $request->input('per_page', 12)));
        $page = $pageOverride ?? max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $baseQuery = FilaEspera::query()
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        $totalRegistros = (clone $baseQuery)
            ->selectRaw("COUNT(DISTINCT DATE_FORMAT(data_pedido, '%Y-%m')) as total")
            ->value('total') ?? 0;

        $series = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(data_pedido, '%Y-%m') as chave_mes, DATE_FORMAT(data_pedido, '%m/%Y') as referencia_mes, COUNT(*) as total_vendas, SUM(dinheiro_limpo) as total_limpo, SUM(dinheiro_sujo) as total_sujo, SUM(dinheiro_limpo + dinheiro_sujo) as faturamento")
            ->groupBy('chave_mes', 'referencia_mes')
            ->orderByDesc('chave_mes')
            ->skip($offset)
            ->take($perPage)
            ->get()
            ->map(function ($linha) {
                return [
                    'referencia' => $linha->referencia_mes,
                    'chave_mes' => $linha->chave_mes,
                    'total_vendas' => (int) $linha->total_vendas,
                    'total_limpo' => (float) ($linha->total_limpo ?? 0),
                    'total_sujo' => (float) ($linha->total_sujo ?? 0),
                    'faturamento' => (float) ($linha->faturamento ?? 0),
                ];
            })
            ->values();

        return [
            'data' => $series,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $totalRegistros,
                'last_page' => max(1, (int) ceil($totalRegistros / max(1, $perPage))),
            ],
        ];
    }

    public function rankingHistorico(string $tipo, Request $request, ?int $perPageOverride = null, ?int $pageOverride = null): array
    {
        [$inicio, $fim] = $this->resolverPeriodo($request);

        $perPage = $perPageOverride ?? max(1, min(50, (int) $request->input('per_page', 10)));
        $page = $pageOverride ?? max(1, (int) $request->input('page', 1));
        $offset = ($page - 1) * $perPage;

        $baseQuery = FilaEspera::query()
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        $totalRegistros = 0;
        $dados = collect();

        switch ($tipo) {
            case 'organizacoes':
                $totalRegistros = (clone $baseQuery)
                    ->selectRaw("COUNT(DISTINCT COALESCE(organizacao_id, 0)) as total")
                    ->value('total') ?? 0;

                $dados = (clone $baseQuery)
                    ->leftJoin('ORGANIZACAO as org', 'org.id', '=', 'FILA_ESPERA.organizacao_id')
                    ->selectRaw("COALESCE(org.nome, 'Sem organizacao') as nome, COUNT(*) as total_vendas, SUM(FILA_ESPERA.dinheiro_limpo + FILA_ESPERA.dinheiro_sujo) as faturamento")
                    ->groupBy('nome')
                    ->orderByDesc('total_vendas')
                    ->skip($offset)
                    ->take($perPage)
                    ->get()
                    ->map(function ($linha) {
                        return [
                            'nome' => $linha->nome,
                            'total_vendas' => (int) $linha->total_vendas,
                            'faturamento' => (float) $linha->faturamento,
                        ];
                    });
                break;

            case 'solicitantes':
                $totalRegistros = (clone $baseQuery)
                    ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(nome, ''), 'NAO INFORMADO')) as total")
                    ->value('total') ?? 0;

                $dados = (clone $baseQuery)
                    ->selectRaw("COALESCE(NULLIF(nome, ''), 'Nao informado') as nome, COUNT(*) as total_vendas, SUM(dinheiro_limpo + dinheiro_sujo) as faturamento")
                    ->groupBy('nome')
                    ->orderByDesc('total_vendas')
                    ->skip($offset)
                    ->take($perPage)
                    ->get()
                    ->map(function ($linha) {
                        return [
                            'nome' => $linha->nome,
                            'total_vendas' => (int) $linha->total_vendas,
                            'faturamento' => (float) $linha->faturamento,
                        ];
                    });
                break;

            case 'responsaveis':
                $totalRegistros = (clone $baseQuery)
                    ->selectRaw("COUNT(DISTINCT COALESCE(usuario_id, 0)) as total")
                    ->value('total') ?? 0;

                $dados = (clone $baseQuery)
                    ->leftJoin('USUARIOS as usr', 'usr.id', '=', 'FILA_ESPERA.usuario_id')
                    ->selectRaw("COALESCE(usr.nome, 'Sem responsavel') as responsavel, COUNT(*) as total_vendas, SUM(FILA_ESPERA.dinheiro_limpo + FILA_ESPERA.dinheiro_sujo) as faturamento")
                    ->groupBy('responsavel')
                    ->orderByDesc('total_vendas')
                    ->skip($offset)
                    ->take($perPage)
                    ->get()
                    ->map(function ($linha) {
                        return [
                            'responsavel' => $linha->responsavel,
                            'total_vendas' => (int) $linha->total_vendas,
                            'faturamento' => (float) $linha->faturamento,
                        ];
                    });
                break;

            default:
                return [
                    'data' => [],
                    'meta' => [
                        'page' => $page,
                        'per_page' => $perPage,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                ];
        }

        $dados = $dados->values();

        return [
            'data' => $dados,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => (int) $totalRegistros,
                'last_page' => max(1, (int) ceil($totalRegistros / max(1, $perPage))),
            ],
        ];
    }




    public function dadosCriacao(): array
    {
        $fila = new FilaEspera();
        $fila->status = FilaEspera::STATUS_PENDENTE;
        $fila->data_pedido = Carbon::now();
        $fila->dinheiro_limpo = 0;
        $fila->dinheiro_sujo = 0;
        $fila->desconto_aplicado = false;
        $fila->desconto_valor = 0;
        $fila->desconto_motivo = null;

        $responsaveis = $this->responsaveis();
        if ($responsaveis->isNotEmpty()) {
            $fila->usuario_id = $responsaveis->first()->id;
        }
        $organizacoes = Organizacao::obterTodos();

        return compact('fila', 'responsaveis', 'organizacoes');
    }

    public function dadosEdicao(int $id): array
    {
        $fila = FilaEspera::with(['organizacao', 'usuario'])->findOrFail($id);
        $responsaveis = $this->responsaveis();
        $organizacoes = Organizacao::obterTodos();

        return compact('fila', 'responsaveis', 'organizacoes');
    }

    public function criar(array $dados): FilaEspera
    {
        return DB::transaction(function () use ($dados) {
            $fila = new FilaEspera();
            $this->preencherFila($fila, $dados, true);
            $fila->save();

            return $fila->fresh(['organizacao', 'usuario']);
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $fila = FilaEspera::findOrFail($id);
            $fila->itens()->delete();
            $fila->delete();
        });
    }

    public function salvar(int $id, array $dados): FilaEspera
    {
        return DB::transaction(function () use ($id, $dados) {
            $fila = FilaEspera::findOrFail($id);
            $this->preencherFila($fila, $dados);
            $fila->save();

            return $fila->fresh(['organizacao', 'usuario']);
        });
    }

    public function dadosVenda(int $id): array
    {
        $fila = FilaEspera::with(['organizacao', 'usuario', 'itens'])->findOrFail($id);
        $responsaveis = $this->responsaveis();
        $produtos = Produto::query()
            ->where('ativo', 1)
            ->with('itens')
            ->orderBy('nome')
            ->get(['id', 'nome', 'quantidade']);

        $precosConfig = config('tabela_preco.precos', []);

        $produtosInfo = $produtos->map(function (Produto $produto) use ($precosConfig) {
            $configProduto = $precosConfig[$produto->id] ?? [];
            $loteMinimo = (int) ($configProduto['lote_minimo'] ?? 1);
            $personalizados = $configProduto['precos'] ?? $configProduto;
            if (isset($personalizados['lote_minimo'])) {
                unset($personalizados['lote_minimo']);
            }

            $precos = [
                'padrao' => ['limpo' => 0, 'sujo' => 0],
                'desconto' => ['limpo' => 0, 'sujo' => 0],
                'alianca' => ['limpo' => 0, 'sujo' => 0],
            ];

            foreach ($personalizados as $tabela => $valores) {
                if (!isset($precos[$tabela]) || !is_array($valores)) {
                    continue;
                }
                $precos[$tabela]['limpo'] = isset($valores['limpo']) ? (float) $valores['limpo'] : $precos[$tabela]['limpo'];
                $precos[$tabela]['sujo'] = isset($valores['sujo']) ? (float) $valores['sujo'] : $precos[$tabela]['sujo'];
            }

            $componentes = $produto->itens->map(function ($item) use ($produto) {
                $porLote = (float) ($item->pivot->quantidade ?? 0);
                $porUnidade = $produto->quantidade > 0 ? $porLote / $produto->quantidade : $porLote;
                return [
                    'id' => $item->id,
                    'nome' => $item->nome,
                    'por_lote' => $porLote,
                    'por_unidade' => $porUnidade,
                ];
            })->values();

            return [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'precos' => $precos,
                'lote_minimo' => max(1, $loteMinimo),
                'componentes' => $componentes,
            ];
        })->keyBy('id');

        // Calcular valores brutos a partir dos itens existentes
        $valoresBrutos = $this->calcularValoresBrutos($fila);

        return [
            'fila' => $fila,
            'responsaveis' => $responsaveis,
            'produtos' => $produtos,
            'produtosInfo' => $produtosInfo,
            'valoresBrutos' => $valoresBrutos,
        ];
    }

    public function registrarVenda(FilaEspera $fila, array $dados): FilaEspera
    {
        $tabelaGlobal = strtolower($dados['tabela_preco_global'] ?? 'padrao');
        $pagamentoTipo = strtolower($dados['pagamento_tipo'] ?? '');
        $pagamentoTipo = in_array($pagamentoTipo, ['limpo', 'sujo', 'ambos'], true) ? $pagamentoTipo : null;
        $pagamentoTipoRegistrado = $pagamentoTipo;
        $statusAnterior = $fila->status;
        $materiaisSaida = [];

        // Verificar estoque disponível no baú do gueto antes de processar
        $bauGuetoId = 2;
        $novoStatus = $dados['status'] ?? $fila->status;
        if (
            $bauGuetoId > 0 &&
            $novoStatus === FilaEspera::STATUS_CONCLUIDO &&
            !empty($dados['produtos'])
        ) {
            $verificacaoEstoque = $this->verificarEstoqueBauGueto($dados['produtos'], $bauGuetoId);
            if (!$verificacaoEstoque['sucesso']) {
                throw new \RuntimeException($verificacaoEstoque['mensagem']);
            }
        }

        $filaAtualizada = DB::transaction(function () use ($fila, $dados, $tabelaGlobal, $pagamentoTipo, &$pagamentoTipoRegistrado, &$materiaisSaida) {

            if (!empty($dados['responsavel'])) {
                $fila->usuario_id = (int) $dados['responsavel'];
            }

            $statusInformado = $dados['status'] ?? FilaEspera::STATUS_EM_ATENDIMENTO;
            if (!in_array($statusInformado, [
                FilaEspera::STATUS_EM_ATENDIMENTO,
                FilaEspera::STATUS_CONCLUIDO,
                FilaEspera::STATUS_CANCELADO,
            ], true)) {
                $statusInformado = FilaEspera::STATUS_EM_ATENDIMENTO;
            }

            $fila->status = $statusInformado;

            if (!empty($dados['observacao'])) {
                $fila->pedido = trim($fila->pedido . PHP_EOL . $dados['observacao']);
            }

            if ($pagamentoTipo) {
                $registroPagamento = 'Pagamento recebido: ' . strtoupper($pagamentoTipo);
                if (!Str::contains($fila->pedido, $registroPagamento)) {
                    $fila->pedido = trim($fila->pedido . PHP_EOL . $registroPagamento);
                }
            }

            $this->preencherFila($fila, [
                'pagamento_tipo' => $pagamentoTipo ?? null,
                'dinheiro_limpo' => $dados['dinheiro_limpo'] ?? null,
                'dinheiro_sujo' => $dados['dinheiro_sujo'] ?? null,
                'desconto_aplicado' => $dados['desconto_aplicado'] ?? 'nao',
                'desconto_valor' => $dados['desconto_valor'] ?? null,
                'desconto_motivo' => $dados['desconto_motivo'] ?? null,
            ]);

            $fila->save();

            $fila->itens()->delete();

            // Se a venda não for cancelada, processar itens normalmente
            if ($statusInformado !== FilaEspera::STATUS_CANCELADO) {
                $produtos = $dados['produto_id'] ?? [];
                $quantidades = $dados['quantidade'] ?? [];
                $observacoes = $dados['item_observacao'] ?? [];
                $precosConfig = config('tabela_preco.precos', []);

                $totalLimpo = 0;
                $totalSujo = 0;

                foreach ($produtos as $idx => $produtoId) {
                    $produtoId = (int) $produtoId;
                    $quantidade = (int) ($quantidades[$idx] ?? 0);
                    $tabela = $tabelaGlobal;
                    $configProduto = $precosConfig[$produtoId] ?? [];
                    $personalizados = $configProduto['precos'] ?? $configProduto;
                    if (isset($personalizados['lote_minimo'])) {
                        unset($personalizados['lote_minimo']);
                    }
                    $precoSelecionado = $personalizados[$tabela] ?? [];
                    $precoPadrao = $personalizados['padrao'] ?? [];
                    $precoLimpo = (float) ($precoSelecionado['limpo'] ?? $precoPadrao['limpo'] ?? 0);
                    $precoSujo = (float) ($precoSelecionado['sujo'] ?? $precoPadrao['sujo'] ?? 0);

                    if ($produtoId <= 0 || $quantidade <= 0) {
                        continue;
                    }

                    $totalLimpo += $precoLimpo * $quantidade;
                    $totalSujo += $precoSujo * $quantidade;

                    $produtoModelo = Produto::with('itens')->find($produtoId);
                    if ($produtoModelo && $produtoModelo->itens->isNotEmpty()) {
                        $loteProducao = (int) ($produtoModelo->quantidade ?? 0);
                        foreach ($produtoModelo->itens as $componente) {
                            $porLote = (float) ($componente->pivot->quantidade ?? 0);
                            if ($porLote <= 0) {
                                continue;
                            }
                            $porUnidade = $loteProducao > 0 ? $porLote / $loteProducao : $porLote;
                            $consumo = $porUnidade * $quantidade;
                            if ($consumo > 0) {
                                $materiaisSaida[$componente->id] = ($materiaisSaida[$componente->id] ?? 0) + $consumo;
                            }
                        }
                    }

                    FilaEsperaItem::create([
                        'fila_espera_id' => $fila->id,
                        'produto_id' => $produtoId,
                        'quantidade' => $quantidade,
                        'observacao' => $observacoes[$idx] ?? null,
                        'tabela_preco' => $tabela ?: 'padrao',
                        'preco_unitario_limpo' => $precoLimpo,
                        'preco_unitario_sujo' => $precoSujo,
                    ]);
                }
            } else {
                // Se cancelado, não há itens nem valores calculados
                $totalLimpo = 0;
                $totalSujo = 0;
            }

            // Aplicar desconto nos valores brutos calculados
            $descontoAplicado = $dados['desconto_aplicado'] === 'sim';
            $valorDesconto = $descontoAplicado ? (float) ($dados['desconto_valor'] ?? 0) : 0;

            $totalLimpoFinal = $totalLimpo;
            $totalSujoFinal = $totalSujo;

            if ($valorDesconto > 0) {
                // O desconto é aplicado primeiro no valor limpo
                if ($valorDesconto <= $totalLimpoFinal) {
                    $totalLimpoFinal -= $valorDesconto;
                } else {
                    $descontoRestante = $valorDesconto - $totalLimpoFinal;
                    $totalLimpoFinal = 0;
                    $totalSujoFinal = max(0, $totalSujoFinal - $descontoRestante);
                }
            }

            $informadoLimpo = array_key_exists('dinheiro_limpo', $dados) && trim((string) $dados['dinheiro_limpo']) !== '';
            $informadoSujo = array_key_exists('dinheiro_sujo', $dados) && trim((string) $dados['dinheiro_sujo']) !== '';

            if (!$informadoLimpo) {
                $fila->dinheiro_limpo = $pagamentoTipo === 'sujo' ? 0 : $totalLimpoFinal;
            }

            if (!$informadoSujo) {
                $fila->dinheiro_sujo = $pagamentoTipo === 'limpo' ? 0 : $totalSujoFinal;
            }

            $fila->save();

            $filaAtualizada = $fila->fresh(['itens.produto', 'usuario', 'organizacao']);
            if ($pagamentoTipo) {
                $filaAtualizada->setAttribute('pagamento_tipo', $pagamentoTipo);
            }
            $pagamentoTipoRegistrado = $pagamentoTipo;

            return $filaAtualizada;
        });



        try {
            $this->notificarVendaDiscord($filaAtualizada, $tabelaGlobal, $pagamentoTipoRegistrado);
        } catch (\Throwable $e) {
            report($e);
        }

        $bauGuetoId = 2;
        if (
            $bauGuetoId > 0 &&
            $statusAnterior !== FilaEspera::STATUS_CONCLUIDO &&
            $filaAtualizada->status === FilaEspera::STATUS_CONCLUIDO &&
            !empty($materiaisSaida)
        ) {
            $organizacao = optional($filaAtualizada->organizacao)->nome ?? optional($filaAtualizada->usuario)->nome ?? 'N/A';
            $observacaoBase = sprintf('VENDA #%d - %s', $filaAtualizada->id, $organizacao);

            // Limpar lançamentos anteriores desta venda
            Lancamento::query()
                ->where('observacao', 'like', "VENDA #{$filaAtualizada->id}%")
                ->delete();

            foreach ($materiaisSaida as $itemId => $quantidade) {
                $quantidade = (int) max(1, ceil($quantidade));
                $this->lancamentoService->salvar([
                    'itens_id' => $itemId,
                    'tipo' => 'SAIDA',
                    'quantidade' => $quantidade,
                    'usuario_id' => $filaAtualizada->usuario_id ?? null,
                    'bau_origem_id' => $bauGuetoId,
                    'observacao' => $observacaoBase,
                    'fabricacao' => true,
                    'venda' => true,
                ]);
            }
        }

        return $filaAtualizada;
    }

    private function notificarVendaDiscord(FilaEspera $fila, string $tabelaGlobal, ?string $pagamentoTipo = null): void
    {
        $botToken = config('services.discord.bot_token');
        $canalVendas = config('services.discord.canal_vendas_id');

        if (empty($botToken) || empty($canalVendas)) {
            return;
        }

        // Só notificar para vendas concluídas ou canceladas
        if (!in_array($fila->status, [FilaEspera::STATUS_CONCLUIDO, FilaEspera::STATUS_CANCELADO])) {
            return;
        }

        if (!$pagamentoTipo && !empty($fila->pagamento_tipo)) {
            $pagamentoTipo = strtolower($fila->pagamento_tipo);
        }

        $organizacaoNome = optional($fila->organizacao)->nome;
        $responsavel = optional($fila->usuario)->nome ?? 'Nao informado';

        // Se não tiver organização, usar o nome da pessoa
        $organizacao = !empty($organizacaoNome) ? $organizacaoNome : $responsavel;

        $tabelaLabel = $this->formatarTabelaLabel($tabelaGlobal);

        $pedidoItens = $fila->itens
            ->map(function (FilaEsperaItem $item) {
                $nome = optional($item->produto)->nome ?? ('Produto #' . $item->produto_id);
                return sprintf('- %d %s', (int) $item->quantidade, $nome);
            })
            ->filter();

        $linhas = [];
        $linhas[] = 'GANGUE/ORGANIZACAO: ' . $organizacao;

        if ($fila->status === FilaEspera::STATUS_CANCELADO) {
            $linhas[] = 'PEDIDO: VENDA CANCELADA';
        } elseif ($pedidoItens->count() === 1) {
            $linhas[] = 'PEDIDO: ' . ltrim($pedidoItens->first(), "- \t\n\r\0\x0B");
        } elseif ($pedidoItens->isNotEmpty()) {
            $linhas[] = 'PEDIDO:';
            foreach ($pedidoItens as $itemLinha) {
                $linhas[] = $itemLinha;
            }
        } else {
            $linhas[] = 'PEDIDO: N/D';
        }

        $linhas[] = 'TABELA: ' . $tabelaLabel;
        $linhas[] = 'STATUS: ' . strtoupper(str_replace('_', ' ', $fila->status));

        if ($pagamentoTipo) {
            $linhas[] = 'PAGAMENTO RECEBIDO EM: ' . strtoupper($pagamentoTipo);
        }

        if ((float) $fila->dinheiro_limpo > 0) {
            $linhas[] = 'VALOR LIMPO: ' . $this->formatarMoeda($fila->dinheiro_limpo);
        }

        if ((float) $fila->dinheiro_sujo > 0) {
            $linhas[] = 'VALOR SUJO: ' . $this->formatarMoeda($fila->dinheiro_sujo);
        }

        if ($fila->desconto_aplicado && (float) $fila->desconto_valor > 0) {
            $linhaDesconto = 'DESCONTO: -' . $this->formatarMoeda($fila->desconto_valor);
            if (!empty($fila->desconto_motivo)) {
                $linhaDesconto .= ' (Motivo: ' . $fila->desconto_motivo . ')';
            }
            $linhas[] = $linhaDesconto;
        }

        $linhas[] = 'DATA DO PEDIDO: ' . $this->formatarDataCurta($fila->data_pedido);
        $linhas[] = 'DATA DA ENTREGA: ' . $this->formatarDataCurta($fila->data_entrega_estimada);
        $linhas[] = 'QUEM FEZ A VENDA: ' . $responsavel;

        $mensagem = implode("\n", array_filter($linhas));

        // Definir cor baseada no status
        $cor = match ($fila->status) {
            FilaEspera::STATUS_CONCLUIDO => 0x00FF00, // Verde para concluído
            FilaEspera::STATUS_CANCELADO => 0xFF0000, // Vermelho para cancelado
            default => 0x4B1E78, // Roxo padrão
        };

        $payload = [
            'embeds' => [
                [
                    'title' => 'VENDAS APP',
                    'description' => $mensagem,
                    'color' => $cor,
                    'footer' => [
                        'text' => sprintf('Pedido #%d • %s', $fila->id, $tabelaLabel),
                    ],
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ];

        $request = Http::withToken($botToken, 'Bot')
            ->acceptJson()
            ->timeout(5);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        $response = $request->post("https://discord.com/api/v10/channels/{$canalVendas}/messages", $payload);

        if ($response->failed()) {
            report(new \RuntimeException(sprintf(
                'Falha ao notificar Discord da venda #%d: %s',
                $fila->id,
                $response->body()
            )));
        }
    }

    private function formatarTabelaLabel(string $tabela): string
    {
        $mapa = [
            'padrao' => 'PADRAO',
            'desconto' => 'DESCONTO',
            'alianca' => 'ALIANCA',
        ];

        return $mapa[strtolower($tabela)] ?? strtoupper($tabela);
    }

    private function formatarDataCurta($data): string
    {
        if (empty($data)) {
            return '-';
        }

        if ($data instanceof Carbon) {
            return $data->format('d/m');
        }

        try {
            return Carbon::parse($data)->format('d/m');
        } catch (\Throwable $e) {
            return '-';
        }
    }

    private function formatarMoeda($valor): string
    {
        return 'R$ ' . number_format((float) $valor, 2, ',', '.');
    }

    private function resolverPeriodo(Request $request): array
    {
        $inicio = $this->normalizarData($request->input('data_inicio'))?->startOfDay();
        $fim = $this->normalizarData($request->input('data_fim'))?->endOfDay();

        if (!$fim) {
            $fim = Carbon::now()->endOfDay();
        }

        if (!$inicio) {
            $inicio = Carbon::now()->subDays(30)->startOfDay();
        }

        if ($inicio->greaterThan($fim)) {
            [$inicio, $fim] = [$fim->copy()->startOfDay(), $inicio->copy()->endOfDay()];
        }

        return [$inicio, $fim];
    }

    private function normalizarData(?string $valor): ?Carbon
    {
        if (empty($valor)) {
            return null;
        }

        try {
            return Carbon::parse($valor);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizarDecimal($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_string($valor)) {
            $valor = trim(str_replace(['R$', 'r$', ' '], '', $valor));
            if (str_contains($valor, ',')) {
                $valor = str_replace('.', '', $valor);
                $valor = str_replace(',', '.', $valor);
            }
        }

        return (float) $valor;
    }

    private function preencherFila(FilaEspera $fila, array $dados, bool $isCreate = false): void
    {
        if (array_key_exists('organizacao_id', $dados)) {
            $fila->organizacao_id = $dados['organizacao_id'] ?: null;
        }

        if (!empty($dados['nome'])) {
            $fila->nome = Str::upper($dados['nome']);
        }

        $dataPedido = $this->normalizarData($dados['data_pedido'] ?? null);
        if ($dataPedido) {
            $fila->data_pedido = $dataPedido;
        } elseif ($isCreate && empty($fila->data_pedido)) {
            $fila->data_pedido = Carbon::now();
        }

        $dataEntrega = $this->normalizarData($dados['data_entrega_estimada'] ?? null);
        if ($dataEntrega || array_key_exists('data_entrega_estimada', $dados)) {
            $fila->data_entrega_estimada = $dataEntrega;
        }

        $usuarioId = $dados['usuario_id'] ?? null;
        if (empty($usuarioId) && $isCreate) {
            $usuarioId = Auth::id() ?: optional($this->responsaveis()->first())->id;
        }
        if (!empty($usuarioId)) {
            $fila->usuario_id = (int) $usuarioId;
        } elseif ($isCreate && empty($fila->usuario_id)) {
            $fila->usuario_id = Auth::id() ?: optional($this->responsaveis()->first())->id ?? 0;
        }

        if (!empty($dados['pedido'])) {
            $fila->pedido = $dados['pedido'];
        }

        if (!empty($dados['status'])) {
            $fila->status = $dados['status'];
        } elseif ($isCreate && empty($fila->status)) {
            $fila->status = FilaEspera::STATUS_PENDENTE;
        }

        if (array_key_exists('dinheiro_limpo', $dados)) {
            $fila->dinheiro_limpo = $this->normalizarDecimal($dados['dinheiro_limpo']);
        }

        if (array_key_exists('dinheiro_sujo', $dados)) {
            $fila->dinheiro_sujo = $this->normalizarDecimal($dados['dinheiro_sujo']);
        }

        if (array_key_exists('desconto_aplicado', $dados)) {
            $aplicado = $dados['desconto_aplicado'];
            if (is_string($aplicado)) {
                $aplicado = in_array(strtolower($aplicado), ['1', 'true', 'sim'], true);
            }
            $fila->desconto_aplicado = (bool) $aplicado;
        }

        if (array_key_exists('desconto_valor', $dados)) {
            $fila->desconto_valor = $this->normalizarDecimal($dados['desconto_valor']);
        }

        if (array_key_exists('desconto_motivo', $dados)) {
            $fila->desconto_motivo = $dados['desconto_motivo'] ?? null;
        }

        if (array_key_exists('pagamento_tipo', $dados)) {
            $valor = $dados['pagamento_tipo'];
            if (is_string($valor)) {
                $valor = strtolower(trim($valor));
                if (!in_array($valor, ['limpo', 'sujo', 'ambos'], true)) {
                    $valor = null;
                }
            } else {
                $valor = null;
            }
            $fila->pagamento_tipo = $valor;
        }
    }

    private function calcularValoresBrutos(FilaEspera $fila): array
    {
        $precosConfig = config('tabela_preco.precos', []);
        $totalLimpo = 0;
        $totalSujo = 0;

        foreach ($fila->itens as $item) {
            $produtoId = $item->produto_id;
            $quantidade = $item->quantidade;
            $tabelaItem = $item->tabela_preco ?? 'padrao';

            $configProduto = $precosConfig[$produtoId] ?? [];
            $personalizados = $configProduto['precos'] ?? $configProduto;

            if (isset($personalizados['lote_minimo'])) {
                unset($personalizados['lote_minimo']);
            }

            $precoSelecionado = $personalizados[$tabelaItem] ?? [];
            $precoPadrao = $personalizados['padrao'] ?? [];

            $precoLimpo = (float) ($precoSelecionado['limpo'] ?? $precoPadrao['limpo'] ?? 0);
            $precoSujo = (float) ($precoSelecionado['sujo'] ?? $precoPadrao['sujo'] ?? 0);

            $totalLimpo += $precoLimpo * $quantidade;
            $totalSujo += $precoSujo * $quantidade;
        }

        return [
            'totalLimpo' => $totalLimpo,
            'totalSujo' => $totalSujo,
        ];
    }



    private function calcularCrescimentoMes($inicio, $fim): float
    {
        // Calcular crescimento comparado ao período anterior
        $diasPeriodo = Carbon::parse($inicio)->diffInDays(Carbon::parse($fim));
        $inicioAnterior = Carbon::parse($inicio)->subDays($diasPeriodo);
        $fimAnterior = Carbon::parse($inicio)->subDay();

        $vendas_atual = FilaEspera::where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim])
            ->sum(DB::raw('dinheiro_limpo + dinheiro_sujo'));

        $vendas_anterior = FilaEspera::where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicioAnterior, $fimAnterior])
            ->sum(DB::raw('dinheiro_limpo + dinheiro_sujo'));

        if ($vendas_anterior == 0) return 0;

        return (($vendas_atual - $vendas_anterior) / $vendas_anterior) * 100;
    }

    private function vendasPorDia($inicio, $fim, $vendedorId = null): Collection
    {
        $query = FilaEspera::query()
            ->select([
                DB::raw('DATE(data_pedido) as data'),
                DB::raw('COUNT(*) as vendas'),
                DB::raw('SUM(dinheiro_limpo + dinheiro_sujo) as total'),
            ])
            ->where('status', FilaEspera::STATUS_CONCLUIDO)
            ->whereBetween('data_pedido', [$inicio, $fim]);

        if ($vendedorId) {
            $query->where('usuario_id', $vendedorId);
        }

        return $query
            ->groupBy(DB::raw('DATE(data_pedido)'))
            ->orderBy('data')
            ->get();
    }

    private function verificarEstoqueBauGueto(array $produtos, int $bauGuetoId): array
    {
        // Verificar se o baú existe
        if (!\App\Models\Baus::where('id', $bauGuetoId)->exists()) {
            return [
                'sucesso' => false,
                'mensagem' => 'Baú do gueto não encontrado. Contacte o administrador.'
            ];
        }

        // Calcular materiais necessários
        $materiaisNecessarios = [];
        $precosConfig = config('tabela_preco.precos', []);

        foreach ($produtos as $produto) {
            $produtoId = (int) $produto['produto_id'];
            $quantidade = (int) $produto['quantidade'];

            if ($quantidade <= 0) continue;

            $produtoModelo = \App\Models\Produto::with('itens')
                ->where('id', $produtoId)
                ->where('ativo', 1)
                ->first();

            if (!$produtoModelo) continue;

            $loteMinimo = $precosConfig[$produtoId]['lote_minimo'] ?? 1;
            $loteProducao = (int) ($produtoModelo->quantidade ?? 0);

            foreach ($produtoModelo->itens as $componente) {
                $porLote = (float) ($componente->pivot->quantidade ?? 0);
                if ($porLote <= 0) continue;

                $porUnidade = $loteProducao > 0 ? $porLote / $loteProducao : $porLote;
                $consumo = $porUnidade * $quantidade;

                if ($consumo > 0) {
                    $materiaisNecessarios[$componente->id] = ($materiaisNecessarios[$componente->id] ?? 0) + $consumo;
                }
            }
        }

        if (empty($materiaisNecessarios)) {
            return ['sucesso' => true, 'mensagem' => 'Nenhum material necessário'];
        }

        // Verificar estoque disponível no baú
        $estoqueAtual = \App\Models\Lancamento::query()
            ->selectRaw('itens_id,
                SUM(CASE
                    WHEN tipo = "ENTRADA" AND bau_destino_id = ? THEN quantidade
                    WHEN tipo = "SAIDA" AND bau_origem_id = ? THEN -quantidade
                    ELSE 0
                END) as saldo')
            ->addBinding([$bauGuetoId, $bauGuetoId])
            ->whereIn('itens_id', array_keys($materiaisNecessarios))
            ->groupBy('itens_id')
            ->get()
            ->keyBy('itens_id');

        $itensInsuficientes = [];
        foreach ($materiaisNecessarios as $itemId => $quantidadeNecessaria) {
            $saldoAtual = $estoqueAtual->get($itemId)->saldo ?? 0;
            $quantidadeNecessariaCeil = (int) ceil($quantidadeNecessaria);

            if ($saldoAtual < $quantidadeNecessariaCeil) {
                $nomeItem = \App\Models\Itens::where('id', $itemId)->value('nome') ?? "Item #{$itemId}";
                $itensInsuficientes[] = "{$nomeItem}: necessário {$quantidadeNecessariaCeil}, disponível {$saldoAtual}";
            }
        }

        if (!empty($itensInsuficientes)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Estoque insuficiente no baú do gueto para os seguintes itens: ' . implode('; ', $itensInsuficientes)
            ];
        }

        return ['sucesso' => true, 'mensagem' => 'Estoque suficiente'];
    }
}
