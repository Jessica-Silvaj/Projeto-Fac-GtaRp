<?php

namespace App\Services;

use App\Models\Situacao;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\SituacaoServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SituacaoService implements SituacaoServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Situacao::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.rh.situacao.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $situacao = empty($id) ? new Situacao() : Situacao::find($id);
        return compact('situacao');
    }

    public function salvar(array $dados): Situacao
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $situacao = Situacao::create([
                    'id' => Utils::getSequence(Situacao::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'ativo' => $dados['ativo'] ?? null,
                ]);
                $this->logger->cadastro('SITUAÇÃO', 'INSERIR', 'NOME: ' . $situacao->nome . ', Inserindo', $situacao->id);
            } else {
                $situacao = Situacao::find($dados['id']);
                $situacao->update([
                    'nome' => Str::upper($dados['nome'] ?? $situacao->nome),
                    'ativo' => $dados['ativo'] ?? $situacao->ativo,
                ]);
                $this->logger->cadastro('SITUAÇÃO', 'ATUALIZAR', 'NOME: ' . $situacao->nome . ', Atualizando', $situacao->id);
            }
            return $situacao;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Situacao::find($id);
            $this->logger->cadastro('SITUAÇÃO', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}

