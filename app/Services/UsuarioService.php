<?php

namespace App\Services;

use App\Models\Funcao;
use App\Models\Usuario;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\UsuarioServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioService implements UsuarioServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Usuario::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.rh.usuario.index'), $request, 10);
    }

    public function dadosEdicao(Request $request, int $id = 0): array
    {
        if ($id === 0) {
            $usuario = new Usuario();
            $usuario->setRelation('funcoes', collect());
        } else {
            $usuario = Usuario::with('funcoes')->findOrFail($id);
        }

        $situacao = \App\Models\Situacao::obterTodos();
        $perfil = \App\Models\Perfil::obterTodos();
        $funcoes = Funcao::obterTodos();

        $selecionadas = collect(old('funcoes', $usuario->funcoes->pluck('id')->all()))
            ->map(fn ($v) => (int) $v)
            ->all();

        return compact('usuario', 'situacao', 'perfil', 'funcoes', 'selecionadas');
    }

    public function salvar(array $dados): Usuario
    {
        return DB::transaction(function () use ($dados) {
            if (empty($dados['id'])) {
                $matriculaExistente = Usuario::obterPorMatricula($dados['matricula'] ?? null);
                if (!empty($matriculaExistente)) {
                    throw new \RuntimeException('Esse passaporte já está sendo utilizado(a).');
                }

                $obj = Usuario::create([
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'matricula' => $dados['matricula'] ?? null,
                    'data_admissao' => date('Y-m-d', \DateTime::createFromFormat('d/m/Y', $dados['data_admissao'])->getTimestamp()),
                    'situacao_id' => $dados['situacao'] ?? null,
                    'perfil_id' => $dados['perfil'] ?? null,
                ]);
                $this->logger->cadastro('USUÁRIO', 'INSERIR', 'NOME: ' . $obj->nome . ', Inserindo', $obj->id);
            } else {
                $obj = Usuario::find($dados['id']);
                $obj->update([
                    'nome' => Str::upper($dados['nome'] ?? $obj->nome),
                    'matricula' => $dados['matricula'] ?? $obj->matricula,
                    'data_admissao' => date('Y-m-d', \DateTime::createFromFormat('d/m/Y', $dados['data_admissao'])->getTimestamp()),
                    'situacao_id' => $dados['situacao'] ?? $obj->situacao_id,
                    'perfil_id' => $dados['perfil'] ?? $obj->perfil_id,
                ]);
                $this->logger->cadastro('USUÁRIO', 'ATUALIZAR', 'NOME: ' . $obj->nome . ', Atualizando', $obj->id);
            }

            if (!empty($dados['senha'])) {
                $obj->senha = Hash::make($dados['senha']);
                $obj->save();
            }

            $ids = collect($dados['funcoes'] ?? [])
                ->filter(fn ($v) => $v !== '' && $v !== null)
                ->map(fn ($v) => (int) $v)
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
            $banco = Usuario::find($id);
            $banco->funcoes()->detach();
            $this->logger->cadastro('USUÁRIO', 'EXCLUIR', 'NOME: ' . $banco->nome . ', Excluindo', $banco->id);
            $banco->delete();
        });
    }
}
