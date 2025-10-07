<?php

namespace App\Providers;

use App\Models\Permissoes;
use App\Models\Usuario;
use App\Policies\PermissoesPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Permissoes::class => PermissoesPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Gates tipados usando o usuário autenticado no guard padrão
        Gate::define('permissao', function (Usuario $usuario, $nomePermissao) {
            return $usuario->hasPermissao($nomePermissao);
        });

        // Alias em PT-BR
        Gate::define('acesso', function (Usuario $usuario, $nomePermissao) {
            return $usuario->hasPermissao($nomePermissao);
        });
    }
}
