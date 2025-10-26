<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrganizacaoRequest;
use App\Services\Contracts\OrganizacaoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;

class OrganizacaoController extends Controller
{
    public function __construct(
        private OrganizacaoServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $list = $this->service->listar($request);
        return view('administracao.fabricacao.organizacao.index', compact('list'));
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        return view('administracao.fabricacao.organizacao.edit', $data);
    }

    public function store(OrganizacaoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.fabricacao.organizacao.index')->with('success', 'A organização foi salva com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar a organização');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'A organização foi excluída com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir a organização');
        }
    }
}
