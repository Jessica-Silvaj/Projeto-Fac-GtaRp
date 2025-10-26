<?php

namespace App\Services;

use App\Models\Funcao;
use App\Models\Permissoes;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\PermissoesServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissoesService implements PermissoesServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger) {}

    public function listar(Request $request): LengthAwarePaginator
    {
        // Usar paginação nativa do Eloquent para melhor performance
        $query = Permissoes::query()->orderBy('nome');

        if (!empty($request->nome)) {
            $query->where('nome', 'LIKE', '%' . Str::upper($request->nome) . '%');
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo);
        }

        return $query->paginate(10)->appends($request->query());
    }

    public function dadosEdicao(Request $request, int $id = 0): array
    {
        if ($id === 0) {
            $permissoes = new Permissoes();
            $permissoes->setRelation('funcoes', collect());
        } else {
            $permissoes = Permissoes::find($id);
        }

        $funcoes = Funcao::obterTodos();

        $selecionadas = collect(old('funcoes', $permissoes->funcoes->pluck('id')->all()))
            ->map(fn($v) => (int) $v)
            ->all();

        return compact('permissoes', 'selecionadas', 'funcoes');
    }

    public function salvar(array $dados): Permissoes
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $obj = Permissoes::create([
                    'id'        => Utils::getSequence(Permissoes::$sequence),
                    'nome'      => $dados['nome'] ?? null,
                    'descricao' => Str::upper($dados['descricao'] ?? ''),
                    'ativo'     => $dados['ativo'] ?? null,
                ]);

                $this->logger->cadastro('PERMISSÕES', 'INSERIR', 'NOME: ' . $obj->nome . ', Inserindo', $obj->id);
            } else {
                $obj = Permissoes::find($dados['id']);
                $obj->update([
                    'nome'      => $dados['nome'] ?? $obj->nome,
                    'descricao' => Str::upper($dados['descricao'] ?? $obj->descricao),
                    'ativo'     => $dados['ativo'] ?? $obj->ativo,
                ]);
                $this->logger->cadastro('PERMISSÕES', 'ATUALIZAR', 'NOME: ' . $obj->nome . ', Atualizando', $obj->id);
            }

            // Funções múltiplas (sync)
            $ids = collect($dados['funcoes'] ?? [])
                ->filter(fn($v) => $v !== '' && $v !== null)
                ->map(fn($v) => (int) $v)
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                $obj->funcoes()->sync([]);
            } else {
                $now = now();
                $payload = [];
                foreach ($ids as $fid) {
                    $payload[$fid] = ['data_atribuicao' => $now];
                }
                $obj->funcoes()->sync($payload);
            }

            return $obj;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $banco = Permissoes::find($id);
            $banco->funcoes()->detach();
            $this->logger->cadastro('PERMISSÕES', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }

    public function filtrosIndex(): array
    {
        return [
            'funcoes' => Funcao::obterTodos(),
        ];
    }
}
