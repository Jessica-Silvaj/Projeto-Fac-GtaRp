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
use Illuminate\Support\Facades\Schema;

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

        // cards com cor, ícone, label, descrição e value
        $cards = [
            [
                'label' => 'Usuários',
                'value' => Usuario::count(),
                'icon' => 'ti-user',
                'description' => 'Contas cadastradas',
                'color' => 'linear-gradient(135deg,#6366f1,#06b6d4)',
                'url' => $canAccess('administracao.rh.usuario.index') ? route('administracao.rh.usuario.index') : null,
            ],
            [
                'label' => 'Perfis ativos',
                'value' => Perfil::where('ativo', 1)->count(),
                'icon' => 'ti-id-badge',
                'description' => 'Perfis liberados',
                'color' => 'linear-gradient(135deg,#f59e0b,#ef4444)',
                'url' => $canAccess('administracao.rh.perfil.index') ? route('administracao.rh.perfil.index') : null,
            ],
            [
                'label' => 'Funções ativas',
                'value' => Funcao::where('ativo', 1)->count(),
                'icon' => 'ti-briefcase',
                'description' => 'Funções disponíveis',
                'color' => 'linear-gradient(135deg,#10b981,#06b6d4)',
                'url' => $canAccess('administracao.rh.funcao.index') ? route('administracao.rh.funcao.index') : null,
            ],
            [
                'label' => 'Baús ativos',
                'value' => Baus::where('ativo', 1)->count(),
                'icon' => 'ti-archive',
                'description' => 'Baús liberados',
                'color' => 'linear-gradient(135deg,#7c3aed,#4f46e5)',
                'url' => $canAccess('administracao.estoque.baus.index') ? route('administracao.estoque.baus.index') : null,
            ],
            [
                'label' => 'Itens ativos',
                'value' => Itens::where('ativo', 1)->count(),
                'icon' => 'ti-package',
                'description' => 'Itens cadastrados',
                'color' => 'linear-gradient(135deg,#06b6d4,#0ea5a3)',
                'url' => $canAccess('administracao.estoque.itens.index') ? route('administracao.estoque.itens.index') : null,
            ],
            [
                'label' => 'Produtos',
                'value' => Produto::where('ativo', 1)->count(),
                'icon' => 'ti-truck',
                'description' => 'Produtos de fabricação',
                'color' => 'linear-gradient(135deg,#ef4444,#f97316)',
                'url' => $canAccess('administracao.fabricacao.produtos.index') ? route('administracao.fabricacao.produtos.index') : null,
            ],
        ];

        $solicitacaoStatusLabels = [
            DiscordSolicitacao::STATUS_PENDENTE => ['label' => 'Pendentes', 'class' => 'badge-warning'],
            DiscordSolicitacao::STATUS_AJUSTE => ['label' => 'Em ajuste', 'class' => 'badge-info'],
            DiscordSolicitacao::STATUS_APROVADA => ['label' => 'Aprovadas', 'class' => 'badge-success'],
            DiscordSolicitacao::STATUS_REJEITADA => ['label' => 'Rejeitadas', 'class' => 'badge-danger'],
        ];

        // Sempre monta o resumo de solicitações (disponível para todos)
        $solicitacaoResumo = [];
        foreach ($solicitacaoStatusLabels as $status => $info) {
            $solicitacaoResumo[$status] = DiscordSolicitacao::where('status', $status)->count();
        }

        // Agora traz as últimas solicitações pendentes independentemente de permissão
        $solicitacoesPendentes = DiscordSolicitacao::query()
            ->where('status', DiscordSolicitacao::STATUS_PENDENTE)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Sempre monta o resumo de lançamentos (visível para todos)
        // Preenche as chaves que a view espera: 'entradas', 'saidas', 'total'
        $entradas = 0;
        $saidas = 0;
        $total = Lancamento::count();

        // Se a tabela tiver coluna "tipo" (ex: 'entrada' / 'saida'), usa essa separação
        if (Schema::hasColumn('lancamentos', 'tipo')) {
            $entradas = Lancamento::where('tipo', 'entrada')->count();
            $saidas = Lancamento::where('tipo', 'saida')->count();
        } else {
            // fallback: usa contagens por período para popular o gráfico (ajuste conforme seu modelo)
            $entradas = Lancamento::whereDate('data_atribuicao', Carbon::today())->count();
            $saidas = Lancamento::whereBetween('data_atribuicao', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])->count();
        }

        $lancamentoResumo = [
            'entradas' => $entradas,
            'saidas' => $saidas,
            'total' => $total,
            // mantém compatibilidade com possíveis usos antigos
            'hoje' => Lancamento::whereDate('data_atribuicao', Carbon::today())->count(),
            'seteDias' => Lancamento::whereBetween('data_atribuicao', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])->count(),
            'fabricacao' => Lancamento::where('fabricacao', true)->count(),
        ];

        // Agora traz os últimos lançamentos também independentemente de permissão
        $ultimosLancamentos = Lancamento::query()
            ->with(['item', 'usuario'])
            ->orderByDesc('data_atribuicao')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

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
