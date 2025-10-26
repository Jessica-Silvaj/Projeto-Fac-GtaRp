<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FilaEspera;
use App\Models\Usuario;
use App\Models\Repasse;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class FinanceiroController extends Controller
{
    public function index(Request $request)
    {
        // Processar filtros
        $dataInicio = null;
        $dataFim = null;
        $statusFiltro = $request->get('status');

        if ($request->filled('data_inicio')) {
            $dataInicio = Carbon::createFromFormat('d/m/Y', $request->data_inicio)->startOfDay();
        }

        if ($request->filled('data_fim')) {
            $dataFim = Carbon::createFromFormat('d/m/Y', $request->data_fim)->endOfDay();
        }

        // Resumo geral do dinheiro com filtros de data
        $resumoQuery = FilaEspera::where('status', FilaEspera::STATUS_CONCLUIDO);

        if ($dataInicio) {
            $resumoQuery->where('data_pedido', '>=', $dataInicio);
        }

        if ($dataFim) {
            $resumoQuery->where('data_pedido', '<=', $dataFim);
        }

        $resumoGeral = $resumoQuery->selectRaw('
                SUM(dinheiro_limpo) as total_limpo,
                SUM(dinheiro_sujo) as total_sujo,
                COUNT(*) as total_vendas
            ')
            ->first();



        // Buscar usuários com saldo (vendas + repasses recebidos - repasses feitos)
        $vendasPorVendedor = collect();

        // Usuários com vendas próprias
        $usuariosComVendas = Usuario::select('id', 'nome')->whereExists(function ($query) use ($dataInicio, $dataFim) {
            $query->select(DB::raw(1))
                ->from('FILA_ESPERA')
                ->whereRaw('FILA_ESPERA.usuario_id = usuarios.id')
                ->where('FILA_ESPERA.status', FilaEspera::STATUS_CONCLUIDO);

            if ($dataInicio) {
                $query->where('FILA_ESPERA.data_pedido', '>=', $dataInicio);
            }

            if ($dataFim) {
                $query->where('FILA_ESPERA.data_pedido', '<=', $dataFim);
            }
        })->get();

        // Usuários que receberam repasses
        $usuariosComRepasses = collect();
        if (Schema::hasTable('repasses')) {
            $usuariosComRepasses = Usuario::select('id', 'nome')->whereExists(function ($query) use ($dataInicio, $dataFim) {
                $query->select(DB::raw(1))
                    ->from('repasses')
                    ->whereRaw('repasses.usuario_repasse_id = usuarios.id')
                    ->where('repasses.status', 'ativo');

                if ($dataInicio) {
                    $query->where('repasses.created_at', '>=', $dataInicio);
                }

                if ($dataFim) {
                    $query->where('repasses.created_at', '<=', $dataFim);
                }
            })->get();
        }

        // Combinar e processar usuários
        $todosUsuarios = $usuariosComVendas->merge($usuariosComRepasses)->keyBy('id');

        foreach ($todosUsuarios as $usuario) {
            $saldo = $this->calcularSaldoVendedor($usuario->id, $dataInicio, $dataFim);

            if ($saldo['total'] > 0) {
                $jaRepassou = $this->verificarSeJaRepassou($usuario->id);

                // Aplicar filtro de status
                if ($statusFiltro) {
                    if ($statusFiltro === 'pendente' && $jaRepassou) continue;
                    if ($statusFiltro === 'repassado' && !$jaRepassou) continue;
                } else {
                    // Se não há filtro específico, não exibir os já repassados
                    if ($jaRepassou) continue;
                }

                $vendasPorVendedor->push((object)[
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'total_limpo' => $saldo['limpo'],
                    'total_sujo' => $saldo['sujo'],
                    'total_vendas' => $this->contarVendasDoUsuario($usuario->id, $dataInicio, $dataFim),
                    'repassado' => $jaRepassou
                ]);
            }
        }

        // Ordenar por valor total decrescente
        $vendasPorVendedor = $vendasPorVendedor->sortByDesc(function ($item) {
            return $item->total_limpo + $item->total_sujo;
        })->values();

        // Lista de usuários para o select
        $usuariosSelect = Usuario::orderBy('nome')
            ->get();

        return view('financeiro.index', compact('resumoGeral', 'vendasPorVendedor', 'usuariosSelect'));
    }

    public function marcarRepasse(Request $request, $vendedorId)
    {
        try {
            // Validação dos dados
            $validator = Validator::make($request->all(), [
                'usuario_repasse_id' => 'required|exists:usuarios,id',
                'valor_limpo' => 'required|numeric|min:0',
                'valor_sujo' => 'required|numeric|min:0',
                'observacoes' => 'nullable|string|max:500'
            ], [
                'usuario_repasse_id.required' => 'Selecione um usuário para receber o repasse.',
                'usuario_repasse_id.exists' => 'Usuário selecionado não existe.',
                'valor_limpo.required' => 'Informe o valor limpo a ser repassado.',
                'valor_limpo.numeric' => 'O valor limpo deve ser numérico.',
                'valor_limpo.min' => 'O valor limpo deve ser maior ou igual a zero.',
                'valor_sujo.required' => 'Informe o valor sujo a ser repassado.',
                'valor_sujo.numeric' => 'O valor sujo deve ser numérico.',
                'valor_sujo.min' => 'O valor sujo deve ser maior ou igual a zero.',
                'observacoes.max' => 'As observações não podem exceder 500 caracteres.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            // Processar os valores
            $valorLimpo = (float) $request->valor_limpo;
            $valorSujo = (float) $request->valor_sujo;
            $valorTotal = $valorLimpo + $valorSujo;

            if ($valorTotal <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'O valor total do repasse deve ser maior que zero.'
                ], 422);
            }

            // Verifica se o vendedor existe
            $vendedor = Usuario::find($vendedorId);
            if (!$vendedor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendedor não encontrado.'
                ], 404);
            }

            // Verificar se o vendedor tem saldo suficiente (vendas + repasses recebidos)
            $saldoVendedor = $this->calcularSaldoVendedor($vendedorId);

            if ($saldoVendedor['limpo'] < $valorLimpo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo limpo insuficiente. Disponível: R$ ' . number_format($saldoVendedor['limpo'], 0, ',', '.')
                ], 422);
            }

            if ($saldoVendedor['sujo'] < $valorSujo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo sujo insuficiente. Disponível: R$ ' . number_format($saldoVendedor['sujo'], 0, ',', '.')
                ], 422);
            }

            DB::beginTransaction();

            // Cria o registro de repasse
            $repasse = Repasse::create([
                'vendedor_id' => $vendedorId,
                'usuario_repasse_id' => $request->usuario_repasse_id,
                'valor_limpo' => $valorLimpo,
                'valor_sujo' => $valorSujo,
                'valor_total' => $valorTotal,
                'observacoes' => $request->observacoes,
                'data_repasse' => Carbon::now(),
                'status' => Repasse::STATUS_ATIVO
            ]);

            // Atualizar o status do vendedor após o repasse
            $statusVendedor = $this->atualizarStatusVendedor($vendedorId);

            // Também verificar se o usuário que recebeu o repasse precisa ter seu status atualizado
            $statusReceptor = $this->atualizarStatusVendedor($request->usuario_repasse_id);

            DB::commit();

            // Dados atualizados após o repasse
            $saldoVendedorAtualizado = $this->calcularSaldoVendedor($vendedorId);
            $usuarioReceptor = Usuario::find($request->usuario_repasse_id);

            return response()->json([
                'success' => true,
                'message' => 'Repasse realizado com sucesso!',
                'data' => [
                    'valor_limpo' => $valorLimpo,
                    'valor_sujo' => $valorSujo,
                    'valor_total' => $valorTotal,
                    'usuario' => $usuarioReceptor->nome,
                    'vendedor_status_atualizado' => $statusVendedor,
                    'saldo_restante_vendedor' => $saldoVendedorAtualizado['total'],
                    'repasse_id' => $repasse->id
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function desfazerRepasse(Request $request, $vendedorId)
    {
        try {
            // Busca o último repasse ativo do vendedor
            $repasse = Repasse::where('vendedor_id', $vendedorId)
                ->where('status', Repasse::STATUS_ATIVO)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$repasse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum repasse ativo encontrado para desfazer.'
                ], 404);
            }

            DB::beginTransaction();

            // Armazenar IDs antes de desfazer
            $usuarioRepasseId = $repasse->usuario_repasse_id;

            // Marca o repasse como desfeito
            $repasse->update(['status' => Repasse::STATUS_DESFEITO]);

            // Recalcular status de ambos os usuários
            $statusVendedor = $this->atualizarStatusVendedor($vendedorId);
            $statusReceptor = $this->atualizarStatusVendedor($usuarioRepasseId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Repasse desfeito com sucesso!',
                'data' => [
                    'vendedor_status_atualizado' => $statusVendedor,
                    'receptor_status_atualizado' => $statusReceptor,
                    'valor_desfeito' => $repasse->valor_total
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Calcula o saldo total de um vendedor (vendas + repasses recebidos - repasses feitos)
     */
    private function calcularSaldoVendedor($vendedorId, $dataInicio = null, $dataFim = null)
    {
        // Vendas do vendedor com filtros de data
        $vendasQuery = FilaEspera::where('usuario_id', $vendedorId)
            ->where('status', FilaEspera::STATUS_CONCLUIDO);

        if ($dataInicio) {
            $vendasQuery->where('data_pedido', '>=', $dataInicio);
        }

        if ($dataFim) {
            $vendasQuery->where('data_pedido', '<=', $dataFim);
        }

        $vendas = $vendasQuery->selectRaw('SUM(dinheiro_limpo) as limpo, SUM(dinheiro_sujo) as sujo')
            ->first();

        // Repasses recebidos e feitos (apenas se tabela existir)
        $repassesRecebidos = (object)['limpo' => 0, 'sujo' => 0];
        $repassesFeitos = (object)['limpo' => 0, 'sujo' => 0];

        if (Schema::hasTable('repasses')) {
            // Repasses recebidos
            $repassesRecebidosQuery = Repasse::where('usuario_repasse_id', $vendedorId)
                ->where('status', Repasse::STATUS_ATIVO);

            if ($dataInicio) $repassesRecebidosQuery->where('created_at', '>=', $dataInicio);
            if ($dataFim) $repassesRecebidosQuery->where('created_at', '<=', $dataFim);

            $repassesRecebidos = $repassesRecebidosQuery->selectRaw('SUM(valor_limpo) as limpo, SUM(valor_sujo) as sujo')
                ->first() ?? $repassesRecebidos;

            // Repasses feitos
            $repassesFeitosQuery = Repasse::where('vendedor_id', $vendedorId)
                ->where('status', Repasse::STATUS_ATIVO);

            if ($dataInicio) $repassesFeitosQuery->where('created_at', '>=', $dataInicio);
            if ($dataFim) $repassesFeitosQuery->where('created_at', '<=', $dataFim);

            $repassesFeitos = $repassesFeitosQuery->selectRaw('SUM(valor_limpo) as limpo, SUM(valor_sujo) as sujo')
                ->first() ?? $repassesFeitos;
        }

        $limpo = ($vendas->limpo ?? 0) + ($repassesRecebidos->limpo ?? 0) - ($repassesFeitos->limpo ?? 0);
        $sujo = ($vendas->sujo ?? 0) + ($repassesRecebidos->sujo ?? 0) - ($repassesFeitos->sujo ?? 0);

        return [
            'limpo' => max(0, $limpo), // Garantir que não seja negativo
            'sujo' => max(0, $sujo),   // Garantir que não seja negativo
            'total' => max(0, $limpo + $sujo)
        ];
    }

    /**
     * Verifica se o usuário tem repasses ativos (foi repassado)
     * Regra: Considera repassado se existe pelo menos um repasse ativo na tabela repasses onde ele é o vendedor
     */
    private function verificarSeJaRepassou($usuarioId)
    {
        // Se não existe tabela de repasses, considera não repassado
        if (!Schema::hasTable('repasses')) {
            return false;
        }

        // Verifica se existe algum repasse ativo onde este usuário é o vendedor
        $temRepasseAtivo = Repasse::where('vendedor_id', $usuarioId)
            ->where('status', Repasse::STATUS_ATIVO)
            ->exists();

        return $temRepasseAtivo;
    }

    /**
     * Conta o número total de vendas do usuário
     */
    private function contarVendasDoUsuario($usuarioId, $dataInicio = null, $dataFim = null)
    {
        $query = FilaEspera::where('usuario_id', $usuarioId)
            ->where('status', FilaEspera::STATUS_CONCLUIDO);

        if ($dataInicio) {
            $query->where('data_pedido', '>=', $dataInicio);
        }

        if ($dataFim) {
            $query->where('data_pedido', '<=', $dataFim);
        }

        return $query->count();
    }

    /**
     * Atualiza o status de "repassado" após um repasse
     */
    private function atualizarStatusVendedor($vendedorId)
    {
        return $this->verificarSeJaRepassou($vendedorId);
    }

    /**
     * Relatório detalhado de repasses
     */
    public function relatorio(Request $request)
    {
        $dataInicio = $request->filled('data_inicio')
            ? Carbon::createFromFormat('d/m/Y', $request->data_inicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dataFim = $request->filled('data_fim')
            ? Carbon::createFromFormat('d/m/Y', $request->data_fim)->endOfDay()
            : Carbon::now()->endOfDay();

        // Relatório de repasses com joins otimizados
        $repasses = DB::table('repasses')
            ->join('usuarios as vendedor', 'repasses.vendedor_id', '=', 'vendedor.id')
            ->join('usuarios as receptor', 'repasses.usuario_repasse_id', '=', 'receptor.id')
            ->select([
                'repasses.*',
                'vendedor.nome as vendedor_nome',
                'receptor.nome as receptor_nome'
            ])
            ->whereBetween('repasses.created_at', [$dataInicio, $dataFim])
            ->when($request->filled('vendedor_id'), function ($query) use ($request) {
                return $query->where('repasses.vendedor_id', $request->vendedor_id);
            })
            ->when($request->filled('status_repasse'), function ($query) use ($request) {
                return $query->where('repasses.status', $request->status_repasse);
            })
            ->orderBy('repasses.created_at', 'desc')
            ->paginate(50);

        // Estatísticas do período
        $estatisticas = [
            'total_repasses' => $repasses->total(),
            'valor_total_repassado' => DB::table('repasses')
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->where('status', 'ativo')
                ->sum('valor_total'),
            'media_repasse' => DB::table('repasses')
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->where('status', 'ativo')
                ->avg('valor_total'),
            'vendedores_ativos' => DB::table('repasses')
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->distinct('vendedor_id')
                ->count(),
        ];

        $usuariosSelect = Usuario::orderBy('nome')->get();

        return view('financeiro.relatorio', compact('repasses', 'estatisticas', 'usuariosSelect', 'dataInicio', 'dataFim'));
    }

    /**
     * Exportar relatório para Excel
     */
    public function exportarRelatorio(Request $request)
    {
        $dataInicio = $request->filled('data_inicio')
            ? Carbon::createFromFormat('d/m/Y', $request->data_inicio)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dataFim = $request->filled('data_fim')
            ? Carbon::createFromFormat('d/m/Y', $request->data_fim)->endOfDay()
            : Carbon::now()->endOfDay();

        $repasses = DB::table('repasses')
            ->join('usuarios as vendedor', 'repasses.vendedor_id', '=', 'vendedor.id')
            ->join('usuarios as receptor', 'repasses.usuario_repasse_id', '=', 'receptor.id')
            ->select([
                'repasses.created_at as data_repasse',
                'vendedor.nome as vendedor',
                'receptor.nome as receptor',
                'repasses.valor_limpo',
                'repasses.valor_sujo',
                'repasses.valor_total',
                'repasses.status',
                'repasses.observacoes'
            ])
            ->whereBetween('repasses.created_at', [$dataInicio, $dataFim])
            ->orderBy('repasses.created_at', 'desc')
            ->get();

        $filename = 'relatorio_repasses_' . $dataInicio->format('Y-m-d') . '_' . $dataFim->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];

        $callback = function () use ($repasses) {
            $file = fopen('php://output', 'w');

            // Cabeçalho CSV
            fputcsv($file, [
                'Data do Repasse',
                'Vendedor',
                'Receptor',
                'Valor Limpo',
                'Valor Sujo',
                'Valor Total',
                'Status',
                'Observações'
            ]);

            // Dados
            foreach ($repasses as $repasse) {
                fputcsv($file, [
                    Carbon::parse($repasse->data_repasse)->format('d/m/Y H:i'),
                    $repasse->vendedor,
                    $repasse->receptor,
                    'R$ ' . number_format($repasse->valor_limpo, 0, ',', '.'),
                    'R$ ' . number_format($repasse->valor_sujo, 0, ',', '.'),
                    'R$ ' . number_format($repasse->valor_total, 0, ',', '.'),
                    ucfirst($repasse->status),
                    $repasse->observacoes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API para notificações de vendas pendentes
     */
    public function notificacoes()
    {
        // Buscar vendas pendentes de repasse (com saldo > 0 e não repassadas)
        $vendasPendentes = collect();

        // Usuários com vendas próprias
        $usuariosComVendas = Usuario::select('id', 'nome')->whereExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('FILA_ESPERA')
                ->whereRaw('FILA_ESPERA.usuario_id = usuarios.id')
                ->where('FILA_ESPERA.status', FilaEspera::STATUS_CONCLUIDO);
        })->get();

        // Usuários que receberam repasses
        $usuariosComRepasses = collect();
        if (Schema::hasTable('repasses')) {
            $usuariosComRepasses = Usuario::select('id', 'nome')->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('repasses')
                    ->whereRaw('repasses.usuario_repasse_id = usuarios.id')
                    ->where('repasses.status', 'ativo');
            })->get();
        }

        // Combinar usuários
        $todosUsuarios = $usuariosComVendas->merge($usuariosComRepasses)->keyBy('id');

        foreach ($todosUsuarios as $usuario) {
            $saldo = $this->calcularSaldoVendedor($usuario->id);

            if ($saldo['total'] > 0) {
                $jaRepassou = $this->verificarSeJaRepassou($usuario->id);

                // Só incluir se ainda não repassou (pendente)
                if (!$jaRepassou) {
                    $ultimaVenda = FilaEspera::where('usuario_id', $usuario->id)
                        ->where('status', FilaEspera::STATUS_CONCLUIDO)
                        ->orderBy('data_pedido', 'desc')
                        ->first();

                    $vendasPendentes->push([
                        'id' => $usuario->id,
                        'nome' => $usuario->nome,
                        'saldo_total' => $saldo['total'],
                        'saldo_limpo' => $saldo['limpo'],
                        'saldo_sujo' => $saldo['sujo'],
                        'total_vendas' => $this->contarVendasDoUsuario($usuario->id),
                        'ultima_venda' => $ultimaVenda ? $ultimaVenda->data_pedido : null,
                        'dias_pendente' => $ultimaVenda ? Carbon::parse($ultimaVenda->data_pedido)->diffInDays(Carbon::now()) : 0
                    ]);
                }
            }
        }

        // Ordenar por valor total (maiores primeiro)
        $vendasPendentes = $vendasPendentes->sortByDesc('saldo_total')->values();

        return response()->json([
            'success' => true,
            'total_pendentes' => $vendasPendentes->count(),
            'valor_total_pendente' => $vendasPendentes->sum('saldo_total'),
            'notificacoes' => $vendasPendentes->take(10) // Limitar a 10 para dropdown
        ]);
    }

    /**
     * Dashboard com KPIs financeiros
     */
    public function dashboard(Request $request)
    {
        // KPIs principais do mês atual
        $kpis = $this->getKpisPrincipais();

        // Evolução diária do mês
        $evolucaoDiaria = $this->getEvolucaoDiaria();

        // Ranking de vendedores
        $rankingVendedores = $this->getRankingVendedores();

        // Distribuição limpo vs sujo
        $distribuicao = $this->getDistribuicaoLimpoSujo();

        // Alertas importantes
        $alertas = $this->getAlertas();

        return view('financeiro.dashboard', compact(
            'kpis',
            'evolucaoDiaria',
            'rankingVendedores',
            'distribuicao',
            'alertas'
        ));
    }

    /**
     * API para dados do dashboard (AJAX)
     */
    public function dashboardApi()
    {
        try {
            return response()->json([
                'success' => true,
                'kpis' => $this->getKpisPrincipais(),
                'evolucao_diaria' => $this->getEvolucaoDiaria(),
                'ranking_vendedores' => $this->getRankingVendedores(),
                'distribuicao' => $this->getDistribuicaoLimpoSujo(),
                'alertas' => $this->getAlertas()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar dados do dashboard: ' . $e->getMessage()
            ], 500);
        }
    }



    // ===============================================
    // MÉTODOS PRIVADOS PARA KPIs E DASHBOARD
    // ===============================================

    private function getKpisPrincipais()
    {
        if (!Schema::hasTable('repasses')) {
            return [
                'mes_atual' => ['repasses' => 0, 'valor' => 0, 'vendedores' => 0, 'media' => 0],
                'mes_anterior' => ['repasses' => 0, 'valor' => 0],
                'crescimento' => ['repasses' => 0, 'valor' => 0]
            ];
        }

        // Mês atual
        $mesAtual = DB::select("
            SELECT
                COUNT(*) as total_repasses,
                COALESCE(SUM(valor_limpo), 0) as total_limpo,
                COALESCE(SUM(valor_sujo), 0) as total_sujo,
                COALESCE(SUM(valor_total), 0) as total_geral,
                COALESCE(AVG(valor_total), 0) as media_repasse,
                COUNT(DISTINCT vendedor_id) as vendedores_ativos
            FROM repasses
            WHERE MONTH(data_repasse) = MONTH(CURDATE())
            AND YEAR(data_repasse) = YEAR(CURDATE())
            AND status = ?
        ", [Repasse::STATUS_ATIVO])[0] ?? null;

        // Mês anterior
        $mesAnterior = DB::select("
            SELECT
                COUNT(*) as total_repasses,
                COALESCE(SUM(valor_total), 0) as total_geral
            FROM repasses
            WHERE MONTH(data_repasse) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND YEAR(data_repasse) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
            AND status = ?
        ", [Repasse::STATUS_ATIVO])[0] ?? null;

        // Cálculo de crescimento
        $crescimentoRepasses = 0;
        $crescimentoValor = 0;

        if ($mesAnterior && $mesAnterior->total_repasses > 0) {
            $crescimentoRepasses = round((($mesAtual->total_repasses - $mesAnterior->total_repasses) / $mesAnterior->total_repasses) * 100, 2);
        }

        if ($mesAnterior && $mesAnterior->total_geral > 0) {
            $crescimentoValor = round((($mesAtual->total_geral - $mesAnterior->total_geral) / $mesAnterior->total_geral) * 100, 2);
        }

        return [
            'mes_atual' => [
                'repasses' => $mesAtual->total_repasses ?? 0,
                'valor' => $mesAtual->total_geral ?? 0,
                'vendedores' => $mesAtual->vendedores_ativos ?? 0,
                'media' => $mesAtual->media_repasse ?? 0,
                'limpo' => $mesAtual->total_limpo ?? 0,
                'sujo' => $mesAtual->total_sujo ?? 0
            ],
            'mes_anterior' => [
                'repasses' => $mesAnterior->total_repasses ?? 0,
                'valor' => $mesAnterior->total_geral ?? 0
            ],
            'crescimento' => [
                'repasses' => $crescimentoRepasses,
                'valor' => $crescimentoValor
            ]
        ];
    }

    private function getEvolucaoDiaria()
    {
        if (!Schema::hasTable('repasses')) return [];

        return DB::select("
            SELECT
                DATE_FORMAT(DATE(data_repasse), '%d/%m') as dia,
                DATE_FORMAT(DATE(data_repasse), '%Y-%m-%d') as data_completa,
                COUNT(*) as quantidade_repasses,
                COALESCE(SUM(valor_limpo), 0) as total_limpo,
                COALESCE(SUM(valor_sujo), 0) as total_sujo,
                COALESCE(SUM(valor_total), 0) as total_dia
            FROM repasses
            WHERE MONTH(data_repasse) = MONTH(CURDATE())
            AND YEAR(data_repasse) = YEAR(CURDATE())
            AND status = ?
            GROUP BY DATE(data_repasse)
            ORDER BY DATE(data_repasse) ASC
        ", [Repasse::STATUS_ATIVO]);
    }

    private function getRankingVendedores()
    {
        if (!Schema::hasTable('repasses')) return [];

        return DB::select("
            SELECT
                vendedor_nome,
                COUNT(*) as total_repasses,
                COALESCE(SUM(valor_limpo), 0) as total_limpo,
                COALESCE(SUM(valor_sujo), 0) as total_sujo,
                COALESCE(SUM(valor_total), 0) as total_geral,
                COALESCE(AVG(valor_total), 0) as media_repasse,
                DATE_FORMAT(MAX(data_repasse), '%d/%m/%Y') as ultimo_repasse
            FROM repasses
            WHERE MONTH(data_repasse) = MONTH(CURDATE())
            AND YEAR(data_repasse) = YEAR(CURDATE())
            AND status = ?
            GROUP BY vendedor_id, vendedor_nome
            ORDER BY total_geral DESC
            LIMIT 10
        ", [Repasse::STATUS_ATIVO]);
    }

    private function getDistribuicaoLimpoSujo()
    {
        if (!Schema::hasTable('repasses')) {
            return ['limpo' => 0, 'sujo' => 0, 'total' => 0, 'percentual_limpo' => 0, 'percentual_sujo' => 0];
        }

        $resultado = DB::select("
            SELECT
                COALESCE(SUM(valor_limpo), 0) as total_limpo,
                COALESCE(SUM(valor_sujo), 0) as total_sujo,
                COALESCE(SUM(valor_total), 0) as total_geral,
                CASE
                    WHEN SUM(valor_total) > 0 THEN ROUND((SUM(valor_limpo) / SUM(valor_total)) * 100, 2)
                    ELSE 0
                END as percentual_limpo,
                CASE
                    WHEN SUM(valor_total) > 0 THEN ROUND((SUM(valor_sujo) / SUM(valor_total)) * 100, 2)
                    ELSE 0
                END as percentual_sujo
            FROM repasses
            WHERE MONTH(data_repasse) = MONTH(CURDATE())
            AND YEAR(data_repasse) = YEAR(CURDATE())
            AND status = ?
        ", [Repasse::STATUS_ATIVO])[0] ?? null;

        return [
            'limpo' => $resultado->total_limpo ?? 0,
            'sujo' => $resultado->total_sujo ?? 0,
            'total' => $resultado->total_geral ?? 0,
            'percentual_limpo' => $resultado->percentual_limpo ?? 0,
            'percentual_sujo' => $resultado->percentual_sujo ?? 0
        ];
    }



    private function getAlertas()
    {
        $alertas = [];

        if (Schema::hasTable('repasses')) {
            // Repasses de valores muito altos
            $repassesAltos = DB::select("
                SELECT
                    vendedor_nome,
                    valor_total as valor_repasse,
                    DATE_FORMAT(data_repasse, '%d/%m/%Y %H:%i') as data_repasse
                FROM repasses r
                WHERE r.status = ?
                AND r.valor_total > (
                    SELECT AVG(valor_total) + (2 * STDDEV(valor_total))
                    FROM repasses
                    WHERE status = ?
                    AND data_repasse >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                )
                AND r.data_repasse >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                ORDER BY r.valor_total DESC
                LIMIT 5
            ", [Repasse::STATUS_ATIVO, Repasse::STATUS_ATIVO]);

            foreach ($repassesAltos as $repasse) {
                $alertas[] = [
                    'tipo' => 'warning',
                    'icone' => 'fas fa-exclamation-triangle',
                    'titulo' => 'Repasse de Valor Alto',
                    'mensagem' => "Repasse de R$ " . number_format($repasse->valor_repasse, 0, ',', '.') . " para {$repasse->vendedor_nome}",
                    'data' => $repasse->data_repasse
                ];
            }

            // Vendedores sem repasse há muito tempo
            $vendedoresSemRepasse = DB::select("
                SELECT
                    rv.vendedor_nome,
                    DATE_FORMAT(MAX(rv.data_repasse), '%d/%m/%Y') as ultimo_repasse,
                    DATEDIFF(CURDATE(), MAX(rv.data_repasse)) as dias_sem_repasse
                FROM repasses rv
                WHERE rv.status = ?
                GROUP BY rv.vendedor_id, rv.vendedor_nome
                HAVING DATEDIFF(CURDATE(), MAX(rv.data_repasse)) > 30
                ORDER BY dias_sem_repasse DESC
                LIMIT 3
            ", [Repasse::STATUS_ATIVO]);

            foreach ($vendedoresSemRepasse as $vendedor) {
                $alertas[] = [
                    'tipo' => 'info',
                    'icone' => 'fas fa-clock',
                    'titulo' => 'Vendedor Inativo',
                    'mensagem' => "{$vendedor->vendedor_nome} sem repasse há {$vendedor->dias_sem_repasse} dias",
                    'data' => $vendedor->ultimo_repasse
                ];
            }
        }

        return $alertas;
    }
}
