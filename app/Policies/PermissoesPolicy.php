<?php

namespace App\Policies;

use App\Models\Permissoes;
use App\Models\Usuario;

class PermissoesPolicy
{
    public function viewAny(Usuario $user): bool
    {
        return $user->hasPermissao('administracao.sistema.permissoes.index');
    }

    public function view(Usuario $user, Permissoes $permissao): bool
    {
        return $user->hasPermissao('administracao.sistema.permissoes.edit');
    }

    public function create(Usuario $user): bool
    {
        return $user->hasPermissao('administracao.sistema.permissoes.store');
    }

    public function update(Usuario $user, Permissoes $permissao): bool
    {
        return $user->hasPermissao('administracao.sistema.permissoes.store');
    }

    public function delete(Usuario $user, Permissoes $permissao): bool
    {
        return $user->hasPermissao('administracao.sistema.permissoes.destroy');
    }
}

