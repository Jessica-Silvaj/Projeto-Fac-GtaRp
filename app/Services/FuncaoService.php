<?php

namespace App\Services;

use App\Models\Funcao;
use App\Services\Contracts\FuncaoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FuncaoService implements FuncaoServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Funcao::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.rh.funcao.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $funcao = empty($id) ? new Funcao() : Funcao::find($id);
        return compact('funcao');
    }

    public function salvar(array $dados): Funcao
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $funcao = Funcao::create([
                    'id' => Utils::getSequence(Funcao::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'ativo' => $dados['ativo'] ?? null,
                ]);
                $this->logger->cadastro('FUNÇÃO', 'INSERIR', 'NOME: ' . $funcao->nome . ', Inserindo', $funcao->id);
            } else {
                $funcao = Funcao::find($dados['id']);
                $funcao->update([
                    'nome' => Str::upper($dados['nome'] ?? $funcao->nome),
                    'ativo' => $dados['ativo'] ?? $funcao->ativo,
                ]);
                $this->logger->cadastro('FUNÇÃO', 'ATUALIZAR', 'NOME: ' . $funcao->nome . ', Atualizando', $funcao->id);
            }

            return $funcao;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Funcao::find($id);
            $this->logger->cadastro('FUNÇÃO', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}

