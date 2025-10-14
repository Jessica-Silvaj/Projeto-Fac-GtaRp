<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Contracts\PermissoesServiceInterface;
use App\Services\PermissoesService;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\LoggingService;
use App\Services\Contracts\FuncaoServiceInterface;
use App\Services\FuncaoService;
use App\Services\Contracts\ItensServiceInterface;
use App\Services\ItensService;
use App\Services\Contracts\LancamentoServiceInterface;
use App\Services\LancamentoService;
use App\Services\Contracts\ProdutoServiceInterface;
use App\Services\ProdutoService;
use App\Services\Contracts\BausServiceInterface;
use App\Services\BausService;
use App\Services\Contracts\UsuarioServiceInterface;
use App\Services\UsuarioService;
use App\Services\Contracts\SituacaoServiceInterface;
use App\Services\SituacaoService;
use App\Services\Contracts\PerfilAdmServiceInterface;
use App\Services\PerfilAdmService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PermissoesServiceInterface::class, PermissoesService::class);
        $this->app->bind(LoggingServiceInterface::class, LoggingService::class);
        $this->app->bind(FuncaoServiceInterface::class, FuncaoService::class);
        $this->app->bind(ItensServiceInterface::class, ItensService::class);
        $this->app->bind(ProdutoServiceInterface::class, ProdutoService::class);
        $this->app->bind(BausServiceInterface::class, BausService::class);
        $this->app->bind(LancamentoServiceInterface::class, LancamentoService::class);
        $this->app->bind(UsuarioServiceInterface::class, UsuarioService::class);
        $this->app->bind(SituacaoServiceInterface::class, SituacaoService::class);
        $this->app->bind(PerfilAdmServiceInterface::class, PerfilAdmService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
