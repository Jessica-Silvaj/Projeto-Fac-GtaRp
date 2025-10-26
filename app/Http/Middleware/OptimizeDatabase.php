<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OptimizeDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Processa a requisição
        $response = $next($request);

        // Fechar conexões desnecessárias após a resposta
        // Isso reduz o número de conexões simultâneas
        DB::disconnect();

        return $response;
    }
}
