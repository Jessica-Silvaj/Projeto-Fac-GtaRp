<?php

namespace App\Http\Controllers;

use App\Http\Requests\SituacaoRequest;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\SituacaoServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SituacaoController extends Controller
{
    public function __construct(
        private SituacaoServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listSituacao = $this->service->listar($request);
        $result = view('administracao.rh.situacao.index', compact('listSituacao'));

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        $result = view('administracao.rh.situacao.edit', $data);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function store(SituacaoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.rh.situacao.index')->with('success', 'A situação foi salva com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar a situação');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'A situação foi excluída com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir a situação');
        }
    }
}
