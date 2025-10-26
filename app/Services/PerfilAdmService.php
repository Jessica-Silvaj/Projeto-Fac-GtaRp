<?php

namespace App\Services;

use App\Models\Perfil;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\PerfilAdmServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PerfilAdmService implements PerfilAdmServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger) {}

    public function listar(Request $request): LengthAwarePaginator
    {
        // Usar paginação nativa do Eloquent para melhor performance
        $query = Perfil::query()->orderBy('nome');

        if (!empty($request->nome)) {
            $query->where('nome', 'LIKE', '%' . Str::upper($request->nome) . '%');
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo);
        }

        return $query->paginate(10)->appends($request->query());
    }

    public function dadosEdicao(int $id = 0): array
    {
        $perfil = empty($id) ? new Perfil() : Perfil::find($id);
        return compact('perfil');
    }

    public function salvar(array $dados): Perfil
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $perfil = Perfil::create([
                    'id' => Utils::getSequence(Perfil::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'ativo' => $dados['ativo'] ?? null,
                ]);
                $this->logger->cadastro('PERFIL', 'INSERIR', 'NOME: ' . $perfil->nome . ', Inserindo', $perfil->id);
            } else {
                $perfil = Perfil::find($dados['id']);
                $perfil->update([
                    'nome' => Str::upper($dados['nome'] ?? $perfil->nome),
                    'ativo' => $dados['ativo'] ?? $perfil->ativo,
                ]);
                $this->logger->cadastro('PERFIL', 'ATUALIZAR', 'NOME: ' . $perfil->nome . ', Atualizando', $perfil->id);
            }
            return $perfil;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Perfil::find($id);
            $this->logger->cadastro('PERFIL', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}
