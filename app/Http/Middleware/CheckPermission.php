<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $routeName = $route?->getName();

        // Se a rota não tiver nome, não há correspondência de permissão
        if (empty($routeName)) {
            return $next($request);
        }

        $usuarioId = session('usuario_id');
        if (empty($usuarioId)) {
            return redirect('/')
                ->withErrors(['matricula' => 'Sessão expirada. Faça login novamente.']);
        }

        $usuario = Usuario::find($usuarioId);
        if (!$usuario) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/')
                ->withErrors(['matricula' => 'Sessão inválida. Faça login novamente.']);
        }

        if (Gate::forUser($usuario)->denies('permissao', $routeName)) {
            abort(403, 'Você não tem permissão para acessar esta funcionalidade.');
        }

        return $next($request);
    }
}

