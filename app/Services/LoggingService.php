<?php

namespace App\Services;

use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Services\Contracts\LoggingServiceInterface;

class LoggingService implements LoggingServiceInterface
{
    public function cadastro(string $area, string $acao, string $mensagem, int|string|null $chave = null): void
    {
        try {
            LogCadastro::inserir($area, $acao, $mensagem, $chave);
        } catch (\Throwable $e) {
            // Evita que falhas de log quebrem o fluxo principal.
        }
    }

    public function excecao(\Throwable $e): void
    {
        try {
            LogExcecao::inserirExcessao($e);
        } catch (\Throwable $inner) {
            // Silencia para não mascarar a exceção original em fluxos de erro.
        }
    }
}

