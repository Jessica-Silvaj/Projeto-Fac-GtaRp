<?php

namespace App\Http\Controllers;

use App\Models\Baus;
use App\Models\DiscordSolicitacao;
use App\Models\Funcao;
use App\Models\Itens;
use App\Models\Lancamento;
use App\Models\Perfil;
use App\Models\Produto;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public static function index(Request $request)
    {
        $request->flash();

        $canAccess = function (?string $permissao) use ($request): bool {
            if (empty($permissao)) {
                return true;
            }

            $usuario = $request->user();
            if (!$usuario) {
                return false;
            }

            return Gate::forUser($usuario)->allows('acesso', $permissao);
        };

        $cards = [
            [
                'label' => 'Usuários',
                'value' => Usuario::count(),
                'icon' => 'ti-user',
                'description' => 'Contas cadastradas no sistema',
                'url' => $canAccess('administracao.rh.usuario.index') ? route('administracao.rh.usuario.index') : null,
            ],
            [
                'label' => 'Perfis ativos',
                'value' => Perfil::where('ativo', 1)->count(),
                'icon' => 'ti-id-badge',
                'description' => 'Perfis liberados para uso',
                'url' => $canAccess('administracao.rh.perfil.index') ? route('administracao.rh.perfil.index') : null,
            ],
            [
                'label' => 'Funções ativas',
                'value' => Funcao::where('ativo', 1)->count(),
                'icon' => 'ti-briefcase',
                'description' => 'Funções disponíveis',
                'url' => $canAccess('administracao.rh.funcao.index') ? route('administracao.rh.funcao.index') : null,
            ],
            [
                'label' => 'Baús ativos',
                'value' => Baus::where('ativo', 1)->count(),
                'icon' => 'ti-archive',
                'description' => 'Baús liberados',
                'url' => $canAccess('administracao.estoque.baus.index') ? route('administracao.estoque.baus.index') : null,
            ],
            [
                'label' => 'Itens ativos',
                'value' => Itens::where('ativo', 1)->count(),
                'icon' => 'ti-package',
                'description' => 'Itens cadastrados',
                'url' => $canAccess('administracao.estoque.itens.index') ? route('administracao.estoque.itens.index') : null,
            ],
            [
                'label' => 'Produtos ativos',
                'value' => Produto::where('ativo', 1)->count(),
                'icon' => 'ti-truck',
                'description' => 'Produtos de fabricação',
                'url' => $canAccess('administracao.fabricacao.produtos.index') ? route('administracao.fabricacao.produtos.index') : null,
            ],
        ];

        $solicitacaoStatusLabels = [
            DiscordSolicitacao::STATUS_PENDENTE => ['label' => 'Pendentes', 'class' => 'badge-warning'],
            DiscordSolicitacao::STATUS_AJUSTE => ['label' => 'Em ajuste', 'class' => 'badge-info'],
            DiscordSolicitacao::STATUS_APROVADA => ['label' => 'Aprovadas', 'class' => 'badge-success'],
            DiscordSolicitacao::STATUS_REJEITADA => ['label' => 'Rejeitadas', 'class' => 'badge-danger'],
        ];

        $solicitacaoResumo = [];
        $solicitacoesPendentes = collect();
        if ($canAccess('bau.lancamentos.solicitacoes.index')) {
            foreach ($solicitacaoStatusLabels as $status => $info) {
                $solicitacaoResumo[$status] = DiscordSolicitacao::where('status', $status)->count();
            }

            $solicitacoesPendentes = DiscordSolicitacao::query()
                ->where('status', DiscordSolicitacao::STATUS_PENDENTE)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $lancamentoResumo = [
            'hoje' => null,
            'seteDias' => null,
            'fabricacao' => null,
        ];
        $ultimosLancamentos = collect();
        if ($canAccess('bau.lancamentos.index')) {
            $inicioSemana = Carbon::now()->subDays(6)->startOfDay();
            $fimSemana = Carbon::now()->endOfDay();

            $lancamentoResumo['hoje'] = Lancamento::whereDate('data_atribuicao', Carbon::today())->count();
            $lancamentoResumo['seteDias'] = Lancamento::whereBetween('data_atribuicao', [$inicioSemana, $fimSemana])->count();
            $lancamentoResumo['fabricacao'] = Lancamento::where('fabricacao', true)->count();

            $ultimosLancamentos = Lancamento::query()
                ->with(['item', 'usuario'])
                ->orderByDesc('data_atribuicao')
                ->orderByDesc('id')
                ->limit(5)
                ->get();
        }

        return view('dashboard.index', [
            'cards' => $cards,
            'solicitacaoStatusLabels' => $solicitacaoStatusLabels,
            'solicitacaoResumo' => $solicitacaoResumo,
            'solicitacoesPendentes' => $solicitacoesPendentes,
            'lancamentoResumo' => $lancamentoResumo,
            'ultimosLancamentos' => $ultimosLancamentos,
        ]);
    }
}
