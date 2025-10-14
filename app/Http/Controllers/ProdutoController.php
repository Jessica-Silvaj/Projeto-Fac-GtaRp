<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ProdutoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Http\Requests\ProdutoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Itens;

class ProdutoController extends Controller
{
    public function __construct(
        private ProdutoServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listProdutos = $this->service->listar($request);
        return view('administracao.fabricacao.produtos.index', compact('listProdutos'));
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        return view('administracao.fabricacao.produtos.edit', $data);
    }

    public function store(ProdutoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.fabricacao.produtos.index')->with('success', 'O produto foi salvo com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o produto');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'O produto foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o produto');
        }
    }

    public function searchItens(Request $request)
    {
        $term = trim((string) $request->get('q', ''));
        $query = Itens::query()->where('ativo', 1);
        if ($term !== '') {
            $query->where('nome', 'LIKE', '%' . Str::upper($term) . '%');
        }
        $items = $query->orderBy('nome')->limit(20)->get(['id', 'nome']);
        return response()->json([
            'results' => $items->map(fn($i) => ['id' => $i->id, 'text' => $i->nome]),
        ]);
    }
}
