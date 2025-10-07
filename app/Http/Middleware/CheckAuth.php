<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $perfil = session('perfil');

        if (is_null($perfil) || (int) $perfil === 9) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')
                ->withErrors(['matricula' => 'Acesso não autorizado.']);
        }

        if (! Auth::check()) {
            return redirect('/')
                ->withErrors(['matricula' => 'Sessão expirada. Faça login.']);
        }

        return $next($request);
    }
}

