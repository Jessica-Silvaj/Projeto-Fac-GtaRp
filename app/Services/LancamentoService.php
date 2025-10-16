<?php

namespace App\Services;

use App\Models\Lancamento;
use App\Models\Itens;
use App\Models\Baus;
use App\Models\Usuario;
use App\Models\Produto;
use App\Services\Contracts\LancamentoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class LancamentoService implements LancamentoServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger) {}

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Lancamento::query()
            ->with(['item', 'bauOrigem', 'bauDestino', 'usuario'])
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('itens_id'), fn($q) => $q->where('itens_id', (int) $request->itens_id))
            ->orderByDesc('data_atribuicao')
            ->get();

        return Utils::arrayPaginator($lista, route('bau.lancamentos.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $lancamento = empty($id) ? new Lancamento() : Lancamento::with(['item', 'bauOrigem', 'bauDestino'])->find($id);
        $bausList = Baus::obterTodos();
        return compact('lancamento', 'bausList');
    }

    public function salvar(array $dados): Lancamento
    {
        return DB::transaction(function () use ($dados) {
            $usuarioId = Session::get('usuario_id') ?: Auth::id();

            $tipo = $dados['tipo'] ?? 'ENTRADA';
            $itensId = (int) ($dados['itens_id'] ?? 0);
            $quantidade = (int) ($dados['quantidade'] ?? 0);
            $observacao = Str::upper($dados['observacao'] ?? '');
            $fabricacao = filter_var($dados['fabricacao'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            // Normaliza origem/destino conforme tipo
            $origem = $dados['bau_origem_id'] ?? null;
            $destino = $dados['bau_destino_id'] ?? null;
            if ($tipo === 'ENTRADA') {
                $origem = null;
            }
            if ($tipo === 'SAIDA') {
                $destino = null;
            }

            if (empty($dados['id'])) {
                $obj = Lancamento::create([
                    'id' => Utils::getSequence(Lancamento::$sequence),
                    'itens_id' => $itensId,
                    'tipo' => $tipo,
                    'quantidade' => $quantidade,
                    'usuario_id' => (int) ($dados['usuario_id'] ?? $usuarioId),
                    'bau_origem_id' => $origem,
                    'bau_destino_id' => $destino,
                    'observacao' => $observacao,
                    'fabricacao' => $fabricacao,
                ]);
                $this->logger->cadastro('LANCAMENTO', 'INSERIR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
            } else {
                $obj = Lancamento::find($dados['id']);
                $obj->update([
                    'itens_id' => $itensId ?: $obj->itens_id,
                    'tipo' => $tipo ?: $obj->tipo,
                    'quantidade' => $quantidade ?: $obj->quantidade,
                    'usuario_id' => (int) ($dados['usuario_id'] ?? $obj->usuario_id ?? $usuarioId),
                    'bau_origem_id' => $origem,
                    'bau_destino_id' => $destino,
                    'observacao' => $observacao ?: $obj->observacao,
                    'fabricacao' => $fabricacao,
                ]);
                $this->logger->cadastro('LANCAMENTO', 'ATUALIZAR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
            }

            $this->registrarMovimentosComponentes($obj, (bool) $fabricacao);

            return $obj;
        });
    }

    private function registrarMovimentosComponentes(Lancamento $principal, bool $fabricacaoAtivo): void
    {
        $prefixo = 'FABRICACÃO AUTOMATICA ' . $principal->id;

        Lancamento::query()
            ->where('observacao', 'like', $prefixo . '%')
            ->delete();

        if (!$fabricacaoAtivo) {
            return;
        }

        $principal->loadMissing('item');
        $itemPrincipal = $principal->item ?: Itens::find($principal->itens_id);
        if (!$itemPrincipal) {
            return;
        }

        $produto = Produto::query()
            ->whereRaw('UPPER(nome) = ?', [Str::upper((string) $itemPrincipal->nome)])
            ->with(['itens' => fn($q) => $q->withPivot('quantidade')])
            ->first();

        if (!$produto || $produto->itens->isEmpty()) {
            return;
        }

        $saidaPorLote = (int) ($produto->quantidade ?? 0);
        if ($saidaPorLote <= 0) {
            $saidaPorLote = 1;
        }

        $fator = $principal->quantidade / $saidaPorLote;
        if ($fator <= 0) {
            return;
        }

        $tipoPrincipal = strtoupper((string) $principal->tipo);
        $bauConsumo = 0;
        if ($tipoPrincipal === 'ENTRADA') {
            $bauConsumo = (int) $principal->bau_destino_id;
        } elseif ($tipoPrincipal === 'SAIDA') {
            $bauConsumo = (int) $principal->bau_origem_id;
        } elseif ($tipoPrincipal === 'TRANSFERENCIA') {
            $bauConsumo = (int) $principal->bau_origem_id ?: (int) $principal->bau_destino_id;
        }
        if ($bauConsumo <= 0) {
            $bauConsumo = (int) ($principal->bau_origem_id ?: $principal->bau_destino_id ?: 0);
        }
        if ($bauConsumo <= 0) {
            return;
        }

        $itemNome = (string) $itemPrincipal->nome;
        $bauRegistro = Baus::find($bauConsumo);
        $consumosComponentes = [];

        foreach ($produto->itens as $componente) {
            $quantidadeComponente = (int) ($componente->pivot->quantidade ?? 0);
            if ($quantidadeComponente <= 0) {
                continue;
            }

            $consumo = (int) ceil($quantidadeComponente * $fator);
            if ($consumo <= 0) {
                continue;
            }

            $saldoAtual = $this->obterSaldoItemNoBau((int) $componente->id, $bauConsumo);
            if ($saldoAtual < $consumo) {
                $mensagem = sprintf(
                    '%s não possui quantidade suficiente do item %s para fabricar %d %s. Necessário %d, disponivel %d.',
                    $bauRegistro->nome ?? ('#' . $bauConsumo),
                    (string) $componente->nome,
                    (int) $principal->quantidade,
                    $itemNome,
                    $consumo,
                    $saldoAtual
                );
                throw ValidationException::withMessages(['fabricacao' => $mensagem]);
            }

            $consumosComponentes[] = [
                'id' => (int) $componente->id,
                'quantidade' => $consumo,
                'nome' => (string) $componente->nome,
            ];
        }

        foreach ($consumosComponentes as $consumoInfo) {
            $observacaoDetalhe = sprintf(
                'FABRICACÃO DE %d %s ➡️ %s',
                (int) $principal->quantidade,
                $itemNome,
                $consumoInfo['nome']
            );

            $observacao = Str::upper(Str::limit($prefixo . ' | ' . $observacaoDetalhe, 255, ''));

            $lancamentoComponente = Lancamento::create([
                'id' => Utils::getSequence(Lancamento::$sequence),
                'itens_id' => $consumoInfo['id'],
                'tipo' => 'SAIDA',
                'quantidade' => $consumoInfo['quantidade'],
                'usuario_id' => (int) $principal->usuario_id,
                'bau_origem_id' => $bauConsumo,
                'bau_destino_id' => null,
                'observacao' => $observacao,
                'fabricacao' => 0,
            ]);

            $this->logger->cadastro(
                'LANCAMENTO',
                'INSERIR',
                'FABRICACAO COMPONENTE AUTO: ' . $observacao,
                $lancamentoComponente->id
            );
        }
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $obj = Lancamento::find($id);
            if ($obj) {
                $this->logger->cadastro('LANCAMENTO', 'EXCLUIR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
                $obj->delete();
            }
        });
    }

    public function historico(Request $request): array
    {
        $inicio = $this->normalizarData($request->get('inicio'));
        $fim = $this->normalizarData($request->get('fim'));
        $itensIdFiltro = (int) ($request->get('itens_id') ?? 0);
        $modo = (string) ($request->get('modo') ?? 'quantidade');
        $isMovimentos = ($modo === 'movimentos');
        $granularidade = (string) ($request->get('granularidade') ?? 'dia'); // dia|semana|mes
        $bauOrigemId = (int) ($request->get('bau_origem_id') ?? 0);
        $bauDestinoId = (int) ($request->get('bau_destino_id') ?? 0);
        $usuarioFiltroId = (int) ($request->get('usuario_id') ?? 0);
        $bauFiltroAny = (int) ($request->get('bau_id') ?? 0);
        if ($bauOrigemId > 0 && $bauDestinoId > 0 && $bauOrigemId === $bauDestinoId) {
            $bauFiltroAny = $bauOrigemId;
        }
        if ($bauFiltroAny > 0) {
            if ($bauOrigemId <= 0) {
                $bauOrigemId = $bauFiltroAny;
            }
            if ($bauDestinoId <= 0) {
                $bauDestinoId = $bauFiltroAny;
            }
        }

        if (!$inicio || !$fim) {
            $fim = Carbon::now()->toDateString();
            $inicio = Carbon::now()->subDays(30)->toDateString();
        }

        $aplicarFiltroBau = function ($query) use ($bauFiltroAny, $bauOrigemId, $bauDestinoId) {
            return $query
                ->when($bauFiltroAny > 0, function ($q) use ($bauFiltroAny) {
                    $q->where(function ($sub) use ($bauFiltroAny) {
                        $sub->where('bau_origem_id', $bauFiltroAny)
                            ->orWhere('bau_destino_id', $bauFiltroAny);
                    });
                })
                ->when($bauFiltroAny === 0 && $bauOrigemId > 0, fn($q) => $q->where('bau_origem_id', $bauOrigemId))
                ->when($bauFiltroAny === 0 && $bauDestinoId > 0, fn($q) => $q->where('bau_destino_id', $bauDestinoId));
        };

        $agregado = $isMovimentos ? 'COUNT(*)' : 'SUM(quantidade)';
        $baseQuery = $aplicarFiltroBau(
            Lancamento::query()
                ->select(DB::raw('DATE(data_atribuicao) as dia'), DB::raw($agregado . ' as total'))
                ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
                ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
        )
            ->whereDate('data_atribuicao', '>=', $inicio)
            ->whereDate('data_atribuicao', '<=', $fim)
            ->groupBy(DB::raw('DATE(data_atribuicao)'))
            ->orderBy('dia');

        // Entradas incluem ENTRADA e TRANSFERENCIA (entrada no destino)
        $entradas = (clone $baseQuery)
            ->whereIn('tipo', ['ENTRADA', 'TRANSFERENCIA'])
            ->get()
            ->map(fn($r) => ['y' => $r->dia, 'total' => (int) $r->total])
            ->values()
            ->all();

        // Saídas incluem SAIDA e TRANSFERENCIA (saída da origem)
        $saidas = (clone $baseQuery)
            ->whereIn('tipo', ['SAIDA', 'TRANSFERENCIA'])
            ->get()
            ->map(fn($r) => ['y' => $r->dia, 'total' => (int) $r->total])
            ->values()
            ->all();

        // Série combinada por dia (para gráfico comparativo)
        $serieMapa = [];
        foreach ($entradas as $e) {
            $serieMapa[$e['y']] = ($serieMapa[$e['y']] ?? ['y' => $e['y'], 'entradas' => 0, 'saidas' => 0]);
            $serieMapa[$e['y']]['entradas'] += $e['total'];
        }
        foreach ($saidas as $s) {
            $serieMapa[$s['y']] = ($serieMapa[$s['y']] ?? ['y' => $s['y'], 'entradas' => 0, 'saidas' => 0]);
            $serieMapa[$s['y']]['saidas'] += $s['total'];
        }
        ksort($serieMapa);
        // Filtro simples por tipo para a visualização (quando informado)
        $tipoFiltro = strtoupper((string) ($request->get('tipo') ?? ''));
        if ($tipoFiltro === 'ENTRADA') {
            $saidas = [];
            foreach ($serieMapa as $k => $v) {
                $v['saidas'] = 0;
                $serieMapa[$k] = $v;
            }
        } elseif ($tipoFiltro === 'SAIDA') {
            $entradas = [];
            foreach ($serieMapa as $k => $v) {
                $v['entradas'] = 0;
                $serieMapa[$k] = $v;
            }
        }

        // Agregação por semana/mês em PHP para portabilidade
        if ($granularidade !== 'dia') {
            $bucket = [];
            foreach ($serieMapa as $d => $val) {
                $c = Carbon::parse($d);
                if ($granularidade === 'semana') {
                    // ISO week label, sort by start of week
                    $label = $c->startOfWeek()->format('o-\S\e\m W'); // Ex.: 2025-Sem 41
                    $key = $c->startOfWeek()->format('Y-m-d');
                } else { // mes
                    $label = $c->format('Y-m');
                    $key = $c->startOfMonth()->format('Y-m-d');
                }
                if (!isset($bucket[$key])) {
                    $bucket[$key] = ['y' => $label, 'entradas' => 0, 'saidas' => 0];
                }
                $bucket[$key]['entradas'] += $val['entradas'];
                $bucket[$key]['saidas'] += $val['saidas'];
            }
            ksort($bucket);
            $serie = array_values($bucket);
        } else {
            $serie = array_values($serieMapa);
        }

        // Saldo acumulado por bucket
        $saldoSerie = [];
        $saldo = 0;
        foreach ($serie as $row) {
            $saldo += (int) ($row['entradas'] ?? 0) - (int) ($row['saidas'] ?? 0);
            $saldoSerie[] = ['y' => $row['y'], 'saldo' => $saldo];
        }

        // Top itens (donut) para entradas e saídas no período
        $entradasPorItemRows = $aplicarFiltroBau(
            Lancamento::query()
                ->select('itens_id', DB::raw($agregado . ' as total'))
                ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
                ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
        )
            ->whereIn('tipo', ['ENTRADA', 'TRANSFERENCIA'])
            ->whereDate('data_atribuicao', '>=', $inicio)
            ->whereDate('data_atribuicao', '<=', $fim)
            ->groupBy('itens_id')
            ->orderByDesc('total')
            ->with('item')
            ->get()
            ->map(fn($r) => [
                'id' => (int) $r->itens_id,
                'label' => optional($r->item)->nome ?? ('Item #' . $r->itens_id),
                'value' => (int) $r->total,
            ])->values()->all();

        // Top 5 + Outros (mantém também a lista completa para "ver mais")
        if (!empty($tipoFiltro) && $tipoFiltro === 'SAIDA') {
            $entradasPorItemRows = [];
        }
        usort($entradasPorItemRows, fn($a, $b) => $b['value'] <=> $a['value']);
        $entradasPorItemTodos = $entradasPorItemRows;
        $outrosValor = 0;
        $entradasPorItem = array_slice($entradasPorItemRows, 0, 5);
        if (count($entradasPorItemRows) > 5) {
            for ($i = 5; $i < count($entradasPorItemRows); $i++) $outrosValor += (int) $entradasPorItemRows[$i]['value'];
        }
        if ($outrosValor > 0) {
            $entradasPorItem[] = ['label' => 'Outros', 'value' => $outrosValor];
        }

        $saidasPorItemRows = $aplicarFiltroBau(
            Lancamento::query()
                ->select('itens_id', DB::raw($agregado . ' as total'))
                ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
                ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
        )
            ->whereIn('tipo', ['SAIDA', 'TRANSFERENCIA'])
            ->whereDate('data_atribuicao', '>=', $inicio)
            ->whereDate('data_atribuicao', '<=', $fim)
            ->groupBy('itens_id')
            ->orderByDesc('total')
            ->with('item')
            ->get()
            ->map(fn($r) => [
                'id' => (int) $r->itens_id,
                'label' => optional($r->item)->nome ?? ('Item #' . $r->itens_id),
                'value' => (int) $r->total,
            ])->values()->all();
        if (!empty($tipoFiltro) && $tipoFiltro === 'ENTRADA') {
            $saidasPorItemRows = [];
        }
        usort($saidasPorItemRows, fn($a, $b) => $b['value'] <=> $a['value']);
        $saidasPorItemTodos = $saidasPorItemRows;
        $outrosSaidas = 0;
        $saidasPorItem = array_slice($saidasPorItemRows, 0, 5);
        if (count($saidasPorItemRows) > 5) {
            for ($i = 5; $i < count($saidasPorItemRows); $i++) $outrosSaidas += (int) $saidasPorItemRows[$i]['value'];
        }
        if ($outrosSaidas > 0) {
            $saidasPorItem[] = ['label' => 'Outros', 'value' => $outrosSaidas];
        }

        // Totais gerais
        $totais = [
            'entradas' => array_sum(array_column($entradas, 'total')),
            'saidas' => array_sum(array_column($saidas, 'total')),
        ];

        $itemSelecionado = null;
        if ($itensIdFiltro > 0) {
            $it = Itens::find($itensIdFiltro);
            if ($it) {
                $itemSelecionado = ['id' => $it->id, 'nome' => $it->nome];
            }
        }
        $bauOrigemSelecionado = null;
        if ($bauOrigemId > 0) {
            $bo = Baus::find($bauOrigemId);
            if ($bo) {
                $bauOrigemSelecionado = ['id' => $bo->id, 'nome' => $bo->nome];
            }
        }
        $bauDestinoSelecionado = null;
        if ($bauDestinoId > 0) {
            $bd = Baus::find($bauDestinoId);
            if ($bd) {
                $bauDestinoSelecionado = ['id' => $bd->id, 'nome' => $bd->nome];
            }
        }
        $usuariosBau = Usuario::query()
            ->whereHas('funcoes', fn($q) => $q->whereRaw('UPPER(FUNCAO.nome) = ?', ['BAU']))
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(fn($u) => ['id' => $u->id, 'nome' => $u->nome])
            ->values()
            ->all();

        return compact('inicio', 'fim', 'entradas', 'saidas', 'serie', 'saldoSerie', 'entradasPorItem', 'saidasPorItem', 'entradasPorItemTodos', 'saidasPorItemTodos', 'totais', 'itensIdFiltro', 'modo', 'granularidade', 'itemSelecionado', 'bauOrigemId', 'bauDestinoId', 'usuarioFiltroId', 'bauOrigemSelecionado', 'bauDestinoSelecionado', 'usuariosBau');
    }

    public function estoqueTotal(Request $request): array
    {
        $inicio = $this->normalizarData($request->get('inicio'));
        $fim = $this->normalizarData($request->get('fim'));
        $itensIdFiltro = (int) ($request->get('itens_id') ?? 0);
        $bauFiltroId = (int) ($request->get('bau_id') ?? 0);
        $usuarioFiltroId = (int) ($request->get('usuario_id') ?? 0);

        $aplicarFiltros = function ($query) use ($inicio, $fim, $itensIdFiltro, $usuarioFiltroId) {
            return $query
                ->when($itensIdFiltro > 0, fn($q) => $q->where('itens_id', $itensIdFiltro))
                ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
                ->when($inicio, fn($q) => $q->whereDate('data_atribuicao', '>=', $inicio))
                ->when($fim, fn($q) => $q->whereDate('data_atribuicao', '<=', $fim));
        };

        $movimentosEntrada = $aplicarFiltros(
            Lancamento::query()
                ->select([
                    'itens_id',
                    DB::raw('bau_destino_id as bau_id'),
                    DB::raw('quantidade as movimento'),
                    DB::raw('data_atribuicao as data_ref'),
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
                    DB::raw('data_atribuicao as data_ref'),
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
            ->when($bauFiltroId > 0, fn($q) => $q->where('bau_id', $bauFiltroId))
            ->havingRaw('SUM(movimento) > 0')
            ->orderBy('itens_id')
            ->get();

        $itensIds = $saldosRaw->pluck('itens_id')->filter()->unique()->values();
        $bauIds = $saldosRaw->pluck('bau_id')->filter()->unique()->values();

        $itensMap = $itensIds->isEmpty()
            ? collect()
            : Itens::whereIn('id', $itensIds)->get()->keyBy('id');
        $bausMap = $bauIds->isEmpty()
            ? collect()
            : Baus::whereIn('id', $bauIds)->get()->keyBy('id');

        $detalhes = $saldosRaw->map(function ($row) use ($itensMap, $bausMap) {
            $item = $itensMap->get($row->itens_id);
            $bau = $bausMap->get($row->bau_id);
            return [
                'itens_id' => (int) $row->itens_id,
                'item_nome' => $item->nome ?? ('Item #' . $row->itens_id),
                'bau_id' => (int) $row->bau_id,
                'bau_nome' => $bau->nome ?? ('Bau #' . $row->bau_id),
                'saldo' => (int) $row->saldo,
            ];
        })
            ->filter(fn($row) => ($row['saldo'] ?? 0) > 0)
            ->sortBy(function ($row) {
                return strtoupper($row['item_nome']) . '|' . strtoupper($row['bau_nome']);
            })->values();

        $resumoItensCollection = $detalhes->groupBy('itens_id')->map(function ($grupo) {
            return [
                'itens_id' => $grupo->first()['itens_id'],
                'item_nome' => $grupo->first()['item_nome'],
                'quantidade' => $grupo->sum('saldo'),
                'baus' => $grupo->count(),
                'locais' => $grupo->map(function ($det) {
                    return [
                        'bau_id' => $det['bau_id'],
                        'bau_nome' => $det['bau_nome'],
                        'quantidade' => $det['saldo'],
                    ];
                })->sortByDesc('quantidade')->values()->all(),
            ];
        });
        $resumoItens = $resumoItensCollection->values()->sortByDesc('quantidade')->values()->all();

        $resumoBausCollection = $detalhes->groupBy('bau_id')->map(function ($grupo) {
            return [
                'bau_id' => $grupo->first()['bau_id'],
                'bau_nome' => $grupo->first()['bau_nome'],
                'quantidade' => $grupo->sum('saldo'),
                'itens' => $grupo->count(),
                'itens_lista' => $grupo->map(function ($det) {
                    return [
                        'itens_id' => $det['itens_id'],
                        'item_nome' => $det['item_nome'],
                        'quantidade' => $det['saldo'],
                    ];
                })->sortByDesc('quantidade')->values()->all(),
            ];
        });
        $resumoBausAll = $resumoBausCollection->values()->sortByDesc('quantidade')->values();
        $resumoBaus = Utils::arrayPaginator(
            $resumoBausAll->all(),
            route('bau.lancamentos.estoque'),
            $request,
            10
        );

        $quantidadeTotal = $detalhes->sum(fn($d) => max(0, $d['saldo']));
        $totais = [
            'quantidade_total' => $quantidadeTotal,
            'itens_unicos' => count($resumoItens),
            'baus_utilizados' => $resumoBausAll->count(),
        ];

        $itemSelecionado = null;
        if ($itensIdFiltro > 0) {
            $it = $itensMap->get($itensIdFiltro) ?? Itens::find($itensIdFiltro);
            if ($it) {
                $itemSelecionado = ['id' => $it->id, 'nome' => $it->nome];
            }
        }

        $bauSelecionado = null;
        if ($bauFiltroId > 0) {
            $bau = $bausMap->get($bauFiltroId) ?? Baus::find($bauFiltroId);
            if ($bau) {
                $bauSelecionado = ['id' => $bau->id, 'nome' => $bau->nome];
            }
        }

        return [
            'inicio' => $inicio,
            'fim' => $fim,
            'itensIdFiltro' => $itensIdFiltro,
            'bauFiltroId' => $bauFiltroId,
            'usuarioFiltroId' => $usuarioFiltroId,
            'itemSelecionado' => $itemSelecionado,
            'bauSelecionado' => $bauSelecionado,
            'detalhes' => $detalhes->values()->all(),
            'resumoItens' => $resumoItens,
            'resumoBaus' => $resumoBaus,
            'totais' => $totais,
        ];
    }

    public function detalhes(Request $request): array
    {
        $inicio = $this->normalizarData($request->get('inicio'));
        $fim = $this->normalizarData($request->get('fim'));
        $granularidade = (string) ($request->get('granularidade') ?? 'dia');
        $bucketKey = (string) ($request->get('key') ?? '');

        if (!$inicio || !$fim) {
            $fim = Carbon::now()->toDateString();
            $inicio = Carbon::now()->subDays(30)->toDateString();
        }

        $start = null;
        $end = null;
        if ($bucketKey) {
            $c = Carbon::parse($bucketKey);
            if ($granularidade === 'semana') {
                $start = $c->copy()->startOfWeek()->toDateString();
                $end = $c->copy()->endOfWeek()->toDateString();
            } elseif ($granularidade === 'mes') {
                $start = $c->copy()->startOfMonth()->toDateString();
                $end = $c->copy()->endOfMonth()->toDateString();
            } else {
                $start = $c->toDateString();
                $end = $c->toDateString();
            }
        } else {
            $start = $inicio;
            $end = $fim;
        }

        $tiposReq = (array) ($request->get('tipos') ?? []);
        if (empty($tiposReq)) {
            $t = strtoupper((string) ($request->get('tipo') ?? ''));
            if ($t !== '') {
                $tiposReq = [$t];
            }
        }
        $tiposReq = array_values(array_intersect(array_map('strtoupper', $tiposReq), ['ENTRADA', 'SAIDA', 'TRANSFERENCIA']));
        if (empty($tiposReq)) $tiposReq = ['ENTRADA', 'SAIDA', 'TRANSFERENCIA'];

        $itensIds = (array) ($request->get('itens_ids') ?? []);
        $itensIds = array_values(array_filter(array_map('intval', $itensIds)));
        $itensId = (int) ($request->get('itens_id') ?? 0);
        if (empty($itensIds) && $itensId > 0) $itensIds = [$itensId];

        $bauOrigemId = (int) ($request->get('bau_origem_id') ?? 0);
        $bauDestinoId = (int) ($request->get('bau_destino_id') ?? 0);
        $bauFiltroAny = (int) ($request->get('bau_id') ?? 0);
        if ($bauOrigemId > 0 && $bauDestinoId > 0 && $bauOrigemId === $bauDestinoId) {
            $bauFiltroAny = $bauOrigemId;
        }
        $usuarioFiltroId = (int) ($request->get('usuario_id') ?? 0);

        $aplicarFiltroBau = function ($query) use ($bauFiltroAny, $bauOrigemId, $bauDestinoId) {
            return $query
                ->when($bauFiltroAny > 0, function ($q) use ($bauFiltroAny) {
                    $q->where(function ($sub) use ($bauFiltroAny) {
                        $sub->where('bau_origem_id', $bauFiltroAny)
                            ->orWhere('bau_destino_id', $bauFiltroAny);
                    });
                })
                ->when($bauFiltroAny === 0 && $bauOrigemId > 0, fn($q) => $q->where('bau_origem_id', $bauOrigemId))
                ->when($bauFiltroAny === 0 && $bauDestinoId > 0, fn($q) => $q->where('bau_destino_id', $bauDestinoId));
        };

        $q = $aplicarFiltroBau(
            Lancamento::query()
                ->with(['item', 'bauOrigem', 'bauDestino', 'usuario'])
                ->whereIn('tipo', $tiposReq)
                ->when(!empty($itensIds), fn($q) => $q->whereIn('itens_id', $itensIds))
        )
            ->when($usuarioFiltroId > 0, fn($q) => $q->where('usuario_id', $usuarioFiltroId))
            ->whereDate('data_atribuicao', '>=', $start)
            ->whereDate('data_atribuicao', '<=', $end)
            ->orderByDesc('data_atribuicao')
            ->limit(100)
            ->get();

        $detalhes = $q->map(function ($r) {
            return [
                'id' => $r->id,
                'data' => $r->data_atribuicao
                    ? Carbon::parse($r->data_atribuicao)->format('d/m/Y')
                    : '',
                'tipo' => (string) $r->tipo,
                'item' => optional($r->item)->nome,
                'quantidade' => (int) $r->quantidade,
                'bau_origem' => optional($r->bauOrigem)->nome,
                'bau_destino' => optional($r->bauDestino)->nome,
                'usuario' => optional($r->usuario)->nome,
                'observacao' => (string) $r->observacao,
                'fabricacao_auto' => Str::startsWith(Str::upper((string) $r->observacao), 'FABRICACAO AUTO'),
            ];
        })->values()->all();

        return ['detalhes' => $detalhes];
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
            // ignorar e tentar o próximo formato
        }

        try {
            return Carbon::parse($valor)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function obterSaldoItemNoBau(int $itemId, int $bauId): int
    {
        if ($itemId <= 0 || $bauId <= 0) {
            return 0;
        }

        $entradas = Lancamento::query()
            ->whereIn('tipo', ['ENTRADA', 'TRANSFERENCIA'])
            ->where('itens_id', $itemId)
            ->where('bau_destino_id', $bauId)
            ->sum('quantidade');

        $saidas = Lancamento::query()
            ->whereIn('tipo', ['SAIDA', 'TRANSFERENCIA'])
            ->where('itens_id', $itemId)
            ->where('bau_origem_id', $bauId)
            ->sum('quantidade');

        return (int) ($entradas - $saidas);
    }

    public function obterOcupacaoBau(int $bauId): int
    {
        if ($bauId <= 0) {
            return 0;
        }

        $entradas = Lancamento::query()
            ->whereIn('tipo', ['ENTRADA', 'TRANSFERENCIA'])
            ->where('bau_destino_id', $bauId)
            ->sum('quantidade');

        $saidas = Lancamento::query()
            ->whereIn('tipo', ['SAIDA', 'TRANSFERENCIA'])
            ->where('bau_origem_id', $bauId)
            ->sum('quantidade');

        return (int) ($entradas - $saidas);
    }
}
