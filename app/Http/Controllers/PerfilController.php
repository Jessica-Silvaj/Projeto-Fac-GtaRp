<?php

namespace App\Http\Controllers;

use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Perfil;
use App\Models\Situacao;
use App\Models\Usuario;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class PerfilController extends Controller
{
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

    public static function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->flash();

            if (empty($request->id)) {
                return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o usuário.');
            }

            $matriculaExistente = Usuario::obterPorMatricula($request->matricula);
            if (!empty($matriculaExistente) && Session::get('matricula') != $request->matricula) {
                return redirect()->back()->with('error', 'Esse passaporte já está sendo utilizado(a).');
            }

            if ($request->senha) {
                $obj['senha'] = Hash::make($request->senha);
            }

            $banco = Usuario::find($request->id);
            $obj = [
                'nome' => Str::upper($request->nome),
                'matricula' => $request->matricula,
                'data_admissao' => date('Y-m-d', DateTime::createFromFormat('d/m/Y', $request->data_admissao)->getTimestamp()),
                'situacao_id' => $request->situacao_id,
                'perfil_id' => $request->perfil_id,
            ];

            $banco->update($obj);
            LogCadastro::inserir("PERFIL", "ATUALIZAR", "NOME: " . $banco->nome . ", Atualizando", $banco->id);
            DB::commit();
            return redirect()->back()->with('success', 'O perfil do usuário foi atualizado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o perfil do usuário' . $e->getMessage());
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
