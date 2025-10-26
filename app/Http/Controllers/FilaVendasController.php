<?php

namespace App\Http\Controllers;

use App\Models\FilaEspera;
use App\Services\Contracts\FilaEsperaServiceInterface;
use App\Http\Requests\FilaEsperaRequest;
use App\Http\Requests\FilaVendaProcessRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class FilaVendasController extends Controller
{
    public function __construct(private FilaEsperaServiceInterface $service) {}

    public function index(Request $request): View
    {
        $statusLabels = $this->statusLabels();

        $fila = $this->service->listar($request);
        $statusResumo = $this->service->resumoStatus($request);
        $responsaveis = $this->service->responsaveis();

        return view('administracao.fabricacao.filaVendas.index', [
            'fila' => $fila,
            'statusLabels' => $statusLabels,
            'statusResumo' => $statusResumo,
            'responsaveis' => $responsaveis,
        ]);
    }

    public function create(): View
    {
        $dados = $this->service->dadosCriacao();

        return view('administracao.fabricacao.filaVendas.edit', [
            'fila' => $dados['fila'],
            'responsaveis' => $dados['responsaveis'],
            'organizacoes' => $dados['organizacoes'],
            'statusLabels' => $this->statusLabels(),
            'isNovo' => true,
        ]);
    }

    public function edit(int $id): View
    {
        $dados = $this->service->dadosEdicao($id);

        return view('administracao.fabricacao.filaVendas.edit', [
            'fila' => $dados['fila'],
            'responsaveis' => $dados['responsaveis'],
            'organizacoes' => $dados['organizacoes'],
            'statusLabels' => $this->statusLabels(),
            'isNovo' => false,
        ]);
    }

    public function store(FilaEsperaRequest $request): RedirectResponse
    {
        $dados = $request->validated();
        $dados['status'] = $dados['status'] ?? FilaEspera::STATUS_PENDENTE;
        $dados['usuario_id'] = $dados['usuario_id'] ?? Auth::id();

        $this->service->criar($dados);

        return redirect()
            ->route('venda.fila.index')
            ->with('success', 'Pedido criado com sucesso.');
    }

    public function update(FilaEsperaRequest $request, int $id): RedirectResponse
    {
        $this->service->salvar($id, $request->validated());

        return redirect()
            ->route('venda.fila.index')
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function historico(Request $request): View
    {
        $dados = $this->service->historicoVendas($request);

        return view('administracao.fabricacao.filaVendas.historico', [
            'periodo' => $dados['periodo'],
            'totais' => $dados['totais'],
            'series' => $dados['series'],
            'rankings' => $dados['rankings'],
            'filtros' => [
                'data_inicio' => $request->input('data_inicio'),
                'data_fim' => $request->input('data_fim'),
            ],
        ]);
    }

    public function historicoSeriesDiarias(Request $request): JsonResponse
    {
        return response()->json($this->service->seriesDiarias($request));
    }

    public function historicoSeriesSemanais(Request $request): JsonResponse
    {
        return response()->json($this->service->seriesSemanais($request));
    }

    public function historicoSeriesMensais(Request $request): JsonResponse
    {
        return response()->json($this->service->seriesMensais($request));
    }

    public function historicoRanking(Request $request, string $tipo): JsonResponse
    {
        return response()->json($this->service->rankingHistorico($tipo, $request));
    }

    public function vender(int $id): View
    {
        $dados = $this->service->dadosVenda($id);

        $statusOptions = [
            FilaEspera::STATUS_EM_ATENDIMENTO => 'Em atendimento',
            FilaEspera::STATUS_CONCLUIDO => 'Concluido',
            FilaEspera::STATUS_CANCELADO => 'Cancelado',
        ];

        return view('administracao.fabricacao.filaVendas.vender', [
            'fila' => $dados['fila'],
            'responsaveis' => $dados['responsaveis'],
            'produtos' => $dados['produtos'],
            'produtosInfo' => $dados['produtosInfo']->toArray(),
            'statusOptions' => $statusOptions,
            'valoresBrutos' => $dados['valoresBrutos'],
        ]);
    }

    public function processarVenda(FilaVendaProcessRequest $request, int $id): RedirectResponse
    {
        $fila = FilaEspera::findOrFail($id);

        try {
            $this->service->registrarVenda($fila, $request->validated());

            return redirect()
                ->route('venda.fila.index')
                ->with('success', 'Venda registrada com sucesso.');
        } catch (\RuntimeException $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->service->excluir($id);

        return redirect()
            ->route('venda.fila.index')
            ->with('success', 'Pedido excluido com sucesso.');
    }

    private function statusLabels(): array
    {
        return [
            FilaEspera::STATUS_PENDENTE => ['label' => 'Pendente', 'class' => 'badge-warning'],
            FilaEspera::STATUS_EM_ATENDIMENTO => ['label' => 'Em atendimento', 'class' => 'badge-info'],
            FilaEspera::STATUS_CONCLUIDO => ['label' => 'Concluido', 'class' => 'badge-success'],
            FilaEspera::STATUS_CANCELADO => ['label' => 'Cancelado', 'class' => 'badge-danger'],
        ];
    }

    /**
     * API para notificações de vendas pendentes na navbar
     */
    public function notificacoesPendentes(): JsonResponse
    {
        // Buscar vendas pendentes (pendente ou em atendimento)
        $vendasPendentes = FilaEspera::with(['usuario', 'organizacao'])
            ->whereIn('status', [FilaEspera::STATUS_PENDENTE, FilaEspera::STATUS_EM_ATENDIMENTO])
            ->orderBy('data_pedido', 'asc')
            ->take(10)
            ->get();

        // Contagem total de vendas pendentes
        $totalPendentes = FilaEspera::whereIn('status', [FilaEspera::STATUS_PENDENTE, FilaEspera::STATUS_EM_ATENDIMENTO])
            ->count();

        // Preparar dados para a notificação
        $notificacoes = $vendasPendentes->map(function ($venda) {
            $diasPendente = now()->diffInDays($venda->data_pedido);
            $urgencia = $this->calcularUrgencia($diasPendente, $venda->status);

            return [
                'id' => $venda->id,
                'cliente' => $venda->usuario ? $venda->usuario->nome : 'Cliente não encontrado',
                'organizacao' => $venda->organizacao ? $venda->organizacao->nome : '-',
                'status' => $venda->status,
                'status_label' => $this->statusLabels()[$venda->status]['label'] ?? 'Desconhecido',
                'data_pedido' => $venda->data_pedido->format('d/m/Y H:i'),
                'dias_pendente' => $diasPendente,
                'urgencia' => $urgencia,
                'produto_principal' => $venda->produto ? $venda->produto->nome : '-'
            ];
        });

        // Estatísticas resumidas
        $pendente = FilaEspera::where('status', FilaEspera::STATUS_PENDENTE)->count();
        $emAtendimento = FilaEspera::where('status', FilaEspera::STATUS_EM_ATENDIMENTO)->count();

        return response()->json([
            'success' => true,
            'total_pendentes' => $totalPendentes,
            'pendente' => $pendente,
            'em_atendimento' => $emAtendimento,
            'notificacoes' => $notificacoes
        ]);
    }

    /**
     * Calcular nível de urgência baseado nos dias pendentes e status
     */
    private function calcularUrgencia(int $diasPendente, string $status): array
    {
        if ($status === FilaEspera::STATUS_EM_ATENDIMENTO) {
            if ($diasPendente > 2) {
                return ['nivel' => 'alta', 'icon' => 'ti-alert', 'color' => 'bg-c-red', 'class' => 'text-danger'];
            } else {
                return ['nivel' => 'media', 'icon' => 'ti-time', 'color' => 'bg-c-yellow', 'class' => 'text-warning'];
            }
        }

        if ($status === FilaEspera::STATUS_PENDENTE) {
            if ($diasPendente > 5) {
                return ['nivel' => 'alta', 'icon' => 'ti-alert', 'color' => 'bg-c-red', 'class' => 'text-danger'];
            } else if ($diasPendente > 2) {
                return ['nivel' => 'media', 'icon' => 'ti-time', 'color' => 'bg-c-yellow', 'class' => 'text-warning'];
            } else {
                return ['nivel' => 'baixa', 'icon' => 'ti-shopping-cart', 'color' => 'bg-c-blue', 'class' => 'text-primary'];
            }
        }

        return ['nivel' => 'baixa', 'icon' => 'ti-shopping-cart', 'color' => 'bg-c-blue', 'class' => 'text-primary'];
    }
}
