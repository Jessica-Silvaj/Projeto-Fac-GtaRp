<?php

namespace App\Services;

use App\Models\Itens;
use App\Services\Contracts\ItensServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ItensService implements ItensServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Itens::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.estoque.itens.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $itens = empty($id) ? new Itens() : Itens::find($id);
        return compact('itens');
    }

    public function salvar(array $dados): Itens
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $itens = Itens::create([
                    'id' => Utils::getSequence(Itens::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'ativo' => $dados['ativo'] ?? null,
                ]);
                $this->logger->cadastro('ITEM', 'INSERIR', 'NOME: ' . $itens->nome . ', Inserindo', $itens->id);
            } else {
                $itens = Itens::find($dados['id']);
                $itens->update([
                    'nome' => Str::upper($dados['nome'] ?? $itens->nome),
                    'ativo' => $dados['ativo'] ?? $itens->ativo,
                ]);
                $this->logger->cadastro('ITEM', 'ATUALIZAR', 'NOME: ' . $itens->nome . ', Atualizando', $itens->id);
            }

            return $itens;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Itens::find($id);
            $this->logger->cadastro('ITEM', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}

