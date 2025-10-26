<?php

namespace App\Http\Controllers;

use App\Services\Contracts\FuncaoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use App\Http\Requests\FuncaoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncaoController extends Controller
{
    public function __construct(
        private FuncaoServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listFuncao = $this->service->listar($request);
        $result = view('administracao.rh.funcao.index', compact('listFuncao'));

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        $result = view('administracao.rh.funcao.edit', $data);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function store(FuncaoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.rh.funcao.index')->with('success', 'A função foi salva com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar a função');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'A função foi excluída com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir a função');
        }
    }
}
