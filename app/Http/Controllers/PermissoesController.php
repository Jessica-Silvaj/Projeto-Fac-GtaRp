<?php

namespace App\Http\Controllers;

use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\PermissoesServiceInterface;
use Illuminate\Http\Request;
use App\Http\Requests\PermissaoRequest;

class PermissoesController extends Controller
{
    private PermissoesServiceInterface $service;
    private LoggingServiceInterface $logger;

    public function __construct(PermissoesServiceInterface $service, LoggingServiceInterface $logger)
    {
        $this->service = $service;
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $listPermissoes = $this->service->listar($request);
        $filtros = $this->service->filtrosIndex();
        return view('administracao.sistema.permissoes.index', array_merge(['listPermissoes' => $listPermissoes], $filtros));
    }

    public function edit(Request $request, $id = 0)
    {
        $data = $this->service->dadosEdicao($request, (int) $id);
        return view('administracao.sistema.permissoes.edit', $data);
    }

    public function store(PermissaoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->back()->with('success', 'A permissões foi atualizado com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o permissões. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'A Permissões foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o permissões');
        }
    }
}
