<?php

namespace App\Services\Contracts;

interface LoggingServiceInterface
{
    /** Registra log de cadastro/alteração/exclusão com área, ação, mensagem e chave. */
    public function cadastro(string $area, string $acao, string $mensagem, int|string|null $chave = null): void;

    /** Registra exceções da aplicação com stack trace. */
    public function excecao(\Throwable $e): void;
}

