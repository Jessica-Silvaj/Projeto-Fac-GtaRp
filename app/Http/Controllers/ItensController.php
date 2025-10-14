<?php

namespace App\Http\Controllers;

use App\Services\Contracts\ItensServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Http\Requests\ItensRequest;
use Illuminate\Http\Request;

class ItensController extends Controller
{
    public function __construct(
        private ItensServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listItens = $this->service->listar($request);
        return view('administracao.estoque.itens.index', compact('listItens'));
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        return view('administracao.estoque.itens.edit', $data);
    }

    public function store(ItensRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.estoque.itens.index')->with('success', 'O item foi salvo com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o item');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'O item foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o item');
        }
    }
}
