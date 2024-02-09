<?php

namespace App\Http\Controllers;

use App\Models\Perfil;
use App\Models\Situacao;
use App\Models\Usuario;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class UsuarioController extends Controller
{
    public static function index($id)
    {
        $usuario = Usuario::find($id);
        $perfil = Perfil::obterTodos();
        $situacao = Situacao::obterTodos();
        $permissao = [1];

        if ($usuario) {
            if (in_array($usuario->perfil_id, $permissao) || $usuario->matricula == Session::get('matricula')) {
                return view('usuario.index', compact('usuario', 'perfil', 'situacao'));
            } else {
                return redirect()->back()->with('error', 'Você não tem acesso a esse usuário.');
            }
        } else {
            return redirect()->back()->with('error', 'Esse usuário não existe no sistema.');
        }
    }

    public static function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->flash();

            if (empty($request->id)) {
                return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o usuário.');
            }

            $matriculaExistente = Usuario::obterPorMatricula($request->matricula);
            if(!empty($matriculaExistente) && Session::get('matricula') != $request->matricula){
                return redirect()->back()->with('error', 'Essa matrícula esta sendo utilizada.');
            }

            if ($request->senha) {
                $obj['senha'] = crypt($request->senha, 'a45zzzz2s');
            }

            $banco = Usuario::find($request->id);
            $obj = [
                'nome' => $request->nome,
                'matricula' => $request->matricula,
                'data_admissao' => date('Y-m-d', DateTime::createFromFormat('d/m/Y', $request->data_admissao)->getTimestamp()),
                'situacao_id' => $request->situacao_id,
                'perfil_id' => $request->perfil_id,
            ];

            $banco->update($obj);
            DB::commit();
            return redirect()->back()->with('success', 'O usuário foi atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o usuário' . $e->getMessage());
        }
    }

    public static function alterarSenha(Request $request)
    {
        try {
            DB::beginTransaction();
            $usuario = Usuario::where('matricula', Session::get('matricula'))->first();

            if ($usuario && $usuario->senha == crypt($request->senhaAtual, 'a45zzzz2s')) {
                $usuario->senha = crypt($request->novaSenha, 'a45zzzz2s');
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
