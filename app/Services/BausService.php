<?php

namespace App\Services;

use App\Models\Baus;
use App\Services\Contracts\BausServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BausService implements BausServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Baus::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.estoque.baus.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $baus = empty($id) ? new Baus() : Baus::find($id);
        return compact('baus');
    }

    public function salvar(array $dados): Baus
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $baus = Baus::create([
                    'id' => Utils::getSequence(Baus::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'ativo' => $dados['ativo'] ?? null,
                ]);
                $this->logger->cadastro('BAUS', 'INSERIR', 'NOME: ' . $baus->nome . ', Inserindo', $baus->id);
            } else {
                $baus = Baus::find($dados['id']);
                $baus->update([
                    'nome' => Str::upper($dados['nome'] ?? $baus->nome),
                    'ativo' => $dados['ativo'] ?? $baus->ativo,
                ]);
                $this->logger->cadastro('BAUS', 'ATUALIZAR', 'NOME: ' . $baus->nome . ', Atualizando', $baus->id);
            }

            return $baus;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Baus::find($id);
            $this->logger->cadastro('BAUS', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}

