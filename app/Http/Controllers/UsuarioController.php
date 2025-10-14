<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsuarioRequest;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\Contracts\UsuarioServiceInterface;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {
    }

    public function index(Request $request)
    {
        $listUsuario = $this->service->listar($request);
        $funcoes = \App\Models\Funcao::obterTodos();
        $situacao = \App\Models\Situacao::obterTodos();
        $perfil = \App\Models\Perfil::obterTodos();
        return view('administracao.rh.usuario.index', compact('situacao', 'funcoes', 'perfil', 'listUsuario'));
    }

    public function edit(Request $request, $id = 0)
    {
        $data = $this->service->dadosEdicao($request, (int) $id);
        return view('administracao.rh.usuario.edit', $data);
    }

    public function store(UsuarioRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->back()->with('success', 'O usuário foi atualizado com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o usuário. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->service->excluir((int) $id);
            return redirect()->back()->with('success', 'O Usuário foi excluído com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o usuário');
        }
    }
}

