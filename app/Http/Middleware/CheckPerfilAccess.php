<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPerfilAccess
{
    public function handle(Request $request, Closure $next)
    {
        $perfil = session('perfil');

        if (is_null($perfil) || (int)$perfil === 9) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')->withErrors(['matricula' => 'Acesso não autorizado.']);
        }

        return $next($request);
    }
}
