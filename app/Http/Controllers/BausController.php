<?php

namespace App\Http\Controllers;

use App\Services\Contracts\BausServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Http\Requests\BausRequest;
use Illuminate\Http\Request;

class BausController extends Controller
{
    public function __construct(
        private BausServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listBaus = $this->service->listar($request);
        return view('administracao.estoque.baus.index', compact('listBaus'));
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        return view('administracao.estoque.baus.edit', $data);
    }

    public function store(BausRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.estoque.baus.index')->with('success', 'O bau foi salvo com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o bau');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'O bau foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o bau');
        }
    }
}
