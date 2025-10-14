<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\Itens;
use App\Services\Contracts\ProdutoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProdutoService implements ProdutoServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Produto::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.fabricacao.produtos.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $produto = empty($id) ? new Produto() : Produto::with('itens')->find($id);
        $itensList = Itens::where('ativo', 1)->orderBy('nome')->get();
        return compact('produto', 'itensList');
    }

    public function salvar(array $dados): Produto
    {
        return DB::transaction(function () use ($dados) {
            $sync = [];
            $shouldSync = array_key_exists('itens', $dados) || !empty($dados['sync_itens']);
            if (!empty($dados['itens']) && is_array($dados['itens'])) {
                foreach ($dados['itens'] as $item) {
                    if (!empty($item['id']) && !empty($item['quantidade'])) {
                        $sync[(int) $item['id']] = ['quantidade' => (int) $item['quantidade']];
                    }
                }
            }
            if (empty($dados['id'])) {
                $produto = Produto::create([
                    'id' => Utils::getSequence(Produto::$sequence),
                    'nome' => Str::upper($dados['nome'] ?? ''),
                    'quantidade' => (int) ($dados['quantidade'] ?? 0),
                    'ativo' => $dados['ativo'] ?? 1,
                ]);
                $this->logger->cadastro('PRODUTO', 'INSERIR', 'NOME: ' . $produto->nome . ', Inserindo', $produto->id);
            } else {
                $produto = Produto::find($dados['id']);
                $produto->update([
                    'nome' => Str::upper($dados['nome'] ?? $produto->nome),
                    'quantidade' => isset($dados['quantidade']) ? (int) $dados['quantidade'] : $produto->quantidade,
                    'ativo' => $dados['ativo'] ?? $produto->ativo,
                ]);
                $this->logger->cadastro('PRODUTO', 'ATUALIZAR', 'NOME: ' . $produto->nome . ', Atualizando', $produto->id);
            }

            // Sync de composição (materiais) apenas se chave 'itens' foi enviada
            if ($shouldSync) {
                $produto->itens()->sync($sync);
            }

            return $produto;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $obj = Produto::find($id);
            if ($obj) {
                $obj->itens()->detach();
                $this->logger->cadastro('PRODUTO', 'EXCLUIR', 'NOME: ' . $obj->nome . ', Excluindo', $obj->id);
                $obj->delete();
            }
        });
    }
}
