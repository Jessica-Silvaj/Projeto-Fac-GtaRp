<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilAdmRequest;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\PerfilAdmServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdmPerfilController extends Controller
{
    public function __construct(
        private PerfilAdmServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listPerfil = $this->service->listar($request);
        $result = view('administracao.rh.perfil.index', compact('listPerfil'));

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function edit($id = 0)
    {
        $data = $this->service->dadosEdicao((int) $id);
        $result = view('administracao.rh.perfil.edit', $data);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function store(PerfilAdmRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('administracao.rh.perfil.index')->with('success', 'O perfil foi salvo com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o perfil');
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'O Perfil foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o perfil');
        }
    }
}
