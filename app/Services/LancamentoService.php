<?php

namespace App\Services;

use App\Models\Lancamento;
use App\Models\Baus;
use App\Services\Contracts\LancamentoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Utils;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\\Support\\Facades\\Session;
use Illuminate\\Support\\Str;
use Illuminate\\Support\\Facades\\Auth;
class LancamentoService implements LancamentoServiceInterface
{
    public function __construct(private LoggingServiceInterface $logger)
    {
    }

    public function listar(Request $request): LengthAwarePaginator
    {
        $lista = Lancamento::query()
            ->with(['item', 'bauOrigem', 'bauDestino', 'usuario'])
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('itens_id'), fn($q) => $q->where('itens_id', (int) $request->itens_id))
            ->orderByDesc('data_atribuicao')
            ->get();

        return Utils::arrayPaginator($lista, route('bau.lancamentos.index'), $request, 10);
    }

    public function dadosEdicao(int $id = 0): array
    {
        $lancamento = empty($id) ? new Lancamento() : Lancamento::with(['item','bauOrigem','bauDestino'])->find($id);
        $bausList = Baus::obterTodos();
        return compact('lancamento', 'bausList');
    }

    public function salvar(array $dados): Lancamento
    {
        return DB::transaction(function () use ($dados) {
            $usuarioId = Session::get('usuario_id') ?: Auth::id();

            $tipo = $dados['tipo'] ?? 'ENTRADA';
            $itensId = (int) ($dados['itens_id'] ?? 0);
            $quantidade = (int) ($dados['quantidade'] ?? 0);
            $observacao = Str::upper($dados['observacao'] ?? '');

            // Normaliza origem/destino conforme tipo
            $origem = $dados['bau_origem_id'] ?? null;
            $destino = $dados['bau_destino_id'] ?? null;
            if ($tipo === 'ENTRADA') { $origem = null; }
            if ($tipo === 'SAIDA') { $destino = null; }

            if (empty($dados['id'])) {
                $obj = Lancamento::create([
                    'id' => Utils::getSequence(Lancamento::$sequence),
                    'itens_id' => $itensId,
                    'tipo' => $tipo,
                    'quantidade' => $quantidade,
                    'usuario_id' => (int) ($dados['usuario_id'] ?? $usuarioId),
                    'bau_origem_id' => $origem,
                    'bau_destino_id' => $destino,
                    'observacao' => $observacao,
                ]);
                $this->logger->cadastro('LANCAMENTO', 'INSERIR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
            } else {
                $obj = Lancamento::find($dados['id']);
                $obj->update([
                    'itens_id' => $itensId ?: $obj->itens_id,
                    'tipo' => $tipo ?: $obj->tipo,
                    'quantidade' => $quantidade ?: $obj->quantidade,
                    'usuario_id' => (int) ($dados['usuario_id'] ?? $obj->usuario_id ?? $usuarioId),
                    'bau_origem_id' => $origem,
                    'bau_destino_id' => $destino,
                    'observacao' => $observacao ?: $obj->observacao,
                ]);
                $this->logger->cadastro('LANCAMENTO', 'ATUALIZAR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
            }

            return $obj;
        });
    }

    public function excluir(int $id): void
    {
        DB::transaction(function () use ($id) {
            $obj = Lancamento::find($id);
            if ($obj) {
                $this->logger->cadastro('LANCAMENTO', 'EXCLUIR', 'TIPO: ' . $obj->tipo . ', QTD: ' . $obj->quantidade, $obj->id);
                $obj->delete();
            }
        });
    }
}


