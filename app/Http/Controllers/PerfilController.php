<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilSelfRequest;
use App\Models\Perfil;
use App\Models\Situacao;
use App\Models\Usuario;
use App\Services\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PerfilController extends Controller
{
    public function __construct(private LoggingServiceInterface $logger) {}

    public static function edit($id)
    {
        $usuario = Usuario::find($id);
        $perfil = Perfil::obterTodos();
        $situacao = Situacao::obterTodos();
        if ($usuario) {
            if ($usuario->matricula == Session::get('matricula')) {
                return view('perfil.edit', compact('usuario', 'perfil', 'situacao'));
            } else {
                return redirect()->back()->with('error', 'Você não tem acesso a esse usuário.');
            }
        } else {
            return redirect()->back()->with('error', 'Esse usuário não existe no sistema.');
        }
    }

    public function store(PerfilSelfRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();

            if (empty($data['id'])) {
                return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o usuário.');
            }

            $matriculaExistente = Usuario::obterPorMatricula($data['matricula']);
            if (!empty($matriculaExistente) && Session::get('matricula') != $data['matricula']) {
                return redirect()->back()->with('error', 'Esse passaporte já está sendo utilizado(a).');
            }

            $banco = Usuario::find($data['id']);
            $obj = [
                'nome' => Str::upper($data['nome']),
                'matricula' => $data['matricula'],
                'data_admissao' => date('Y-m-d', \DateTime::createFromFormat('d/m/Y', $data['data_admissao'])->getTimestamp()),
                'situacao_id' => $data['situacao_id'],
                'perfil_id' => $data['perfil_id'],
            ];

            if (!empty($data['senha'])) {
                $obj['senha'] =  crypt($data['senha'], 'a45zzzz2s');
            }

            $banco->update($obj);
            DB::commit();
            return redirect()->back()->with('success', 'O perfil do usuário foi atualizado com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao atualizar o perfil do usuário: ' . $e->getMessage());
        }
    }

    public static function alterarSenha(Request $request)
    {
        try {
            DB::beginTransaction();
            $usuario = Usuario::where('matricula', Session::get('matricula'))->first();
            if ($usuario && $usuario->senha == crypt($request->SenhaAtual, 'a45zzzz2s')) {
                $usuario->senha = crypt($request->NovaSenha, 'a45zzzz2s');
                $usuario->save();
            } else {
                throw new \Exception('A senha atual é inválida.');
            }

            DB::commit();
            return redirect()->back()->with('success', 'Senha alterada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocorreu um erro ao alterar a senha: ' . $e->getMessage());
        }
    }
}
