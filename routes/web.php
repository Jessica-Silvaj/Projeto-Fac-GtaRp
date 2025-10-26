<?php

use App\Http\Controllers\AdmPerfilController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BausController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FuncaoController;
use App\Http\Controllers\ItensController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\LancamentoController;
use App\Http\Controllers\FilaVendasController;
use App\Http\Controllers\OrganizacaoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\SituacaoController;
use App\Http\Controllers\SolicitacaoDiscordController;
use App\Http\Controllers\UsuarioController;
use App\Models\Situacao;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get("/logout", [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::middleware('auth.check')->group(function () {
    // DASBHOARD
    Route::get("/dashboard", [DashboardController::class, 'index'])->name('dashboard');
    // PERFIL
    // Route::get("/perfil/usuario/{id}", [PerfilController::class, 'edit'])->name('perfil.edit');
    // Route::post("/perfil/usuario", [PerfilController::class, 'store'])->name('perfil.store');
    Route::post("/perfil/usuario/alterarSenha", [PerfilController::class, 'alterarSenha'])->name('usuario.alterarSenha');

    Route::middleware('perm')->group(function () {
        // ADMINISTRAÇÃO (Protegido por permissão baseada no nome da rota)
        //  -- RESCURSOS HUMANOS
        //  --- Usuario
        Route::get("/administracao/rh/usuario/index", [UsuarioController::class, 'index'])->name('administracao.rh.usuario.index');
        Route::get("/administracao/rh/usuario/edit/{id?}", [UsuarioController::class, 'edit'])->name('administracao.rh.usuario.edit');
        Route::delete('/administracao/rh/usuario/delete/{id}', [UsuarioController::class, 'destroy'])->name('administracao.rh.usuario.destroy');
        Route::post("/administracao/rh/usuario/store", [UsuarioController::class, 'store'])->name('administracao.rh.usuario.store');

        //  --- Perfil
        Route::get("/administracao/rh/perfil/index", [AdmPerfilController::class, 'index'])->name('administracao.rh.perfil.index');
        Route::get("/administracao/rh/perfil/edit/{id?}", [AdmPerfilController::class, 'edit'])->name('administracao.rh.perfil.edit');
        Route::delete('/administracao/rh/perfil/delete/{id}', [AdmPerfilController::class, 'destroy'])->name('administracao.rh.perfil.destroy');
        Route::post("/administracao/rh/perfil/store", [AdmPerfilController::class, 'store'])->name('administracao.rh.perfil.store');

        //  --- Situação
        Route::get("/administracao/rh/situacao/index", [SituacaoController::class, 'index'])->name('administracao.rh.situacao.index');
        Route::get("/administracao/rh/situacao/edit/{id?}", [SituacaoController::class, 'edit'])->name('administracao.rh.situacao.edit');
        Route::delete('/administracao/rh/situacao/delete/{id}', [SituacaoController::class, 'destroy'])->name('administracao.rh.situacao.destroy');
        Route::post("/administracao/rh/situacao/store", [SituacaoController::class, 'store'])->name('administracao.rh.situacao.store');

        //  --- Função
        Route::get("/administracao/rh/funcao/index", [FuncaoController::class, 'index'])->name('administracao.rh.funcao.index');
        Route::get("/administracao/rh/funcao/edit/{id?}", [FuncaoController::class, 'edit'])->name('administracao.rh.funcao.edit');
        Route::delete('/administracao/rh/funcao/delete/{id}', [FuncaoController::class, 'destroy'])->name('administracao.rh.funcao.destroy');
        Route::post("/administracao/rh/funcao/store", [FuncaoController::class, 'store'])->name('administracao.rh.funcao.store');

        // Frequência
        Route::prefix('administracao/rh/frequencia')->name('administracao.rh.frequencia.')->group(function () {
            Route::get('index', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'index'])->name('index');
            Route::get('historico', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'historico'])->name('historico');
            Route::get('relatorio-detalhado', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'relatorioDetalhado'])->name('relatorio.detalhado');
            Route::get('relatorio', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'relatorio'])->name('relatorio');
            Route::post('registrar-falta', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'registrarFalta'])->name('registrar.falta');
            Route::post('remover-falta', [App\Http\Controllers\Administracao\RH\FrequenciaController::class, 'removerFalta'])->name('remover.falta');
        });

        // Permissoes

        //  -- ESTOQUE
        //  --- Itens
        Route::get("/administracao/estoque/itens/index", [ItensController::class, 'index'])->name('administracao.estoque.itens.index');
        Route::get("/administracao/estoque/itens/edit/{id?}", [ItensController::class, 'edit'])->name('administracao.estoque.itens.edit');
        Route::delete('/administracao/estoque/itens/delete/{id}', [ItensController::class, 'destroy'])->name('administracao.estoque.itens.destroy');
        Route::post("/administracao/estoque/itens/store", [ItensController::class, 'store'])->name('administracao.estoque.itens.store');

        //  --- Produtos (Fabricação)
        Route::prefix('administracao/fabricacao/produtos')->name('administracao.fabricacao.produtos.')->group(function () {
            Route::get('index', [ProdutoController::class, 'index'])->name('index');
            Route::get('edit/{id?}', [ProdutoController::class, 'edit'])->name('edit');
            Route::post('store', [ProdutoController::class, 'store'])->name('store');
            Route::delete('delete/{id}', [ProdutoController::class, 'destroy'])->name('destroy');
            Route::get('itens/search', [ProdutoController::class, 'searchItens'])->name('itens.search');
        });

        Route::prefix('administracao/fabricacao/organizacao')->name('administracao.fabricacao.organizacao.')->group(function () {
            Route::get('index', [OrganizacaoController::class, 'index'])->name('index');
            Route::get('edit/{id?}', [OrganizacaoController::class, 'edit'])->name('edit');
            Route::post('store', [OrganizacaoController::class, 'store'])->name('store');
            Route::delete('delete/{id}', [OrganizacaoController::class, 'destroy'])->name('destroy');
        });

        //  --- Baús
        Route::get("/administracao/estoque/baus/index", [BausController::class, 'index'])->name('administracao.estoque.baus.index');
        Route::get("/administracao/estoque/baus/edit/{id?}", [BausController::class, 'edit'])->name('administracao.estoque.baus.edit');
        Route::delete('/administracao/estoque/baus/delete/{id}', [BausController::class, 'destroy'])->name('administracao.estoque.baus.destroy');
        Route::post("/administracao/estoque/baus/store", [BausController::class, 'store'])->name('administracao.estoque.baus.store');

        //  -- SISTEMA
        //  --- Permissões
        Route::prefix('administracao/sistema')->name('administracao.sistema.')->group(function () {
            Route::get('permissoes/index', [PermissoesController::class, 'index'])->name('permissoes.index');
            Route::get('permissoes/edit/{id?}', [PermissoesController::class, 'edit'])->name('permissoes.edit');
            Route::delete('permissoes/delete/{id}', [PermissoesController::class, 'destroy'])->name('permissoes.destroy');
            Route::post('permissoes/store', [PermissoesController::class, 'store'])->name('permissoes.store');
            Route::get('configuracao/anomalia', [\App\Http\Controllers\Administracao\Sistema\ConfiguracaoAnomaliaController::class, 'edit'])->name('configuracao.anomalia.edit');
            Route::post('configuracao/anomalia', [\App\Http\Controllers\Administracao\Sistema\ConfiguracaoAnomaliaController::class, 'update'])->name('configuracao.anomalia.update');
        });
    });

    // CONTROLE BAU (fora do admin/perm) - tipo painel
    Route::prefix('bau/lancamentos')->name('bau.lancamentos.')->group(function () {
        Route::get('index', [LancamentoController::class, 'index'])->name('index');
        Route::get('edit/{id?}', [LancamentoController::class, 'edit'])->name('edit');
        Route::post('store', [LancamentoController::class, 'store'])->name('store');
        Route::delete('delete/{id}', [LancamentoController::class, 'destroy'])->name('destroy');
        // Busca de baús (AJAX) para selects do módulo
        Route::get('bau/baus/search', [LancamentoController::class, 'searchBaus'])->name('bau.baus.search');
        Route::prefix('solicitacoes')->name('solicitacoes.')->group(function () {
            Route::get('/', [SolicitacaoDiscordController::class, 'index'])->name('index');
            Route::get('{solicitacao}/editar', [SolicitacaoDiscordController::class, 'edit'])->name('edit');
            Route::put('{solicitacao}', [SolicitacaoDiscordController::class, 'update'])->name('update');
            Route::post('{solicitacao}/aprovar', [SolicitacaoDiscordController::class, 'aprovar'])->name('aprovar');
            Route::post('{solicitacao}/rejeitar', [SolicitacaoDiscordController::class, 'rejeitar'])->name('rejeitar');
            Route::get('navbar', [SolicitacaoDiscordController::class, 'navbar'])->name('navbar');
        });
        Route::get('historico', [LancamentoController::class, 'historico'])->name('historico');
        Route::get('historico/csv', [LancamentoController::class, 'historicoCsv'])->name('historico.csv');
        Route::get('historico/json', [LancamentoController::class, 'historicoJson'])->name('historico.json');
        Route::get('historico/detalhes', [LancamentoController::class, 'historicoDetalhes'])->name('historico.detalhes');
        Route::get('estoque-total', [LancamentoController::class, 'estoqueTotal'])->name('estoque');
        Route::get('estoque-total/csv', [LancamentoController::class, 'estoqueTotalCsv'])->name('estoque.csv');
        Route::get('anomalias', [\App\Http\Controllers\AnomaliaController::class, 'index'])->name('anomalias');
        Route::get('anomalias/navbar', [\App\Http\Controllers\AnomaliaController::class, 'navbar'])->name('anomalias.navbar');
    });

    Route::prefix('venda/fila')->name('venda.fila.')->group(function () {
        Route::get('historico', [FilaVendasController::class, 'historico'])->name('historico');
        Route::get('historico/series/diarias', [FilaVendasController::class, 'historicoSeriesDiarias'])->name('historico.series.diarias');
        Route::get('historico/series/semanais', [FilaVendasController::class, 'historicoSeriesSemanais'])->name('historico.series.semanais');
        Route::get('historico/series/mensais', [FilaVendasController::class, 'historicoSeriesMensais'])->name('historico.series.mensais');
        Route::get('historico/ranking/{tipo}', [FilaVendasController::class, 'historicoRanking'])->name('historico.ranking');
        Route::get('index', [FilaVendasController::class, 'index'])->name('index');
        Route::get('notificacoes-pendentes', [FilaVendasController::class, 'notificacoesPendentes'])->name('notificacoes-pendentes');
        Route::get('create', [FilaVendasController::class, 'create'])->name('create');
        Route::post('store', [FilaVendasController::class, 'store'])->name('store');
        Route::get('edit/{id}', [FilaVendasController::class, 'edit'])->name('edit');
        Route::post('update/{id}', [FilaVendasController::class, 'update'])->name('update');
        Route::get('vender/{id}', [FilaVendasController::class, 'vender'])->name('vender');
        Route::post('vender/{id}', [FilaVendasController::class, 'processarVenda'])->name('vender.processar');
        Route::delete('{id}', [FilaVendasController::class, 'destroy'])->name('destroy');
    });

    // Rotas do Financeiro
    Route::prefix('financeiro')->name('financeiro.')->group(function () {
        Route::get('/', [App\Http\Controllers\FinanceiroController::class, 'index'])->name('index');
        Route::get('dashboard', [App\Http\Controllers\FinanceiroController::class, 'dashboard'])->name('dashboard');
        Route::get('dashboard/api', [App\Http\Controllers\FinanceiroController::class, 'dashboardApi'])->name('dashboard.api');

        Route::get('relatorio', [App\Http\Controllers\FinanceiroController::class, 'relatorio'])->name('relatorio');
        Route::get('relatorio/exportar', [App\Http\Controllers\FinanceiroController::class, 'exportarRelatorio'])->name('relatorio.exportar');
        Route::get('notificacoes', [App\Http\Controllers\FinanceiroController::class, 'notificacoes'])->name('notificacoes');
        Route::post('repasse/{vendedorId}', [App\Http\Controllers\FinanceiroController::class, 'marcarRepasse'])->name('repasse');
        Route::delete('repasse/{vendedorId}', [App\Http\Controllers\FinanceiroController::class, 'desfazerRepasse'])->name('desfazer-repasse');
    });
});

require __DIR__ . '/auth.php';
