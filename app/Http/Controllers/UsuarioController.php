<?php

namespace App\Http\Controllers;

use App\Models\Funcao;
use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Perfil;
use App\Models\Situacao;
use App\Models\Usuario;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $funcoes = Funcao::obterTodos();
        $situacao = Situacao::obterTodos();
        $perfil = Perfil::obterTodos();
        $listUsuario = Usuario::obterPorFiltros($request);
        $listUsuario = Utils::arrayPaginator($listUsuario, route('administracao.rh.situacao.index'), $request, 10);
        return view('administracao.rh.usuario.index', compact('situacao', 'funcoes', 'perfil', 'listUsuario'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $usuario = new Usuario();
            $usuario->setRelation('funcoes', collect());
        } else {
            $usuario = Usuario::with('funcoes')->findOrFail($id);
        }

        $situacao = Situacao::obterTodos();
        $perfil = Perfil::obterTodos();
        $funcoes = Funcao::obterTodos();

        $selecionadas = collect(old('funcoes', $usuario->funcoes->pluck('id')->all()))->map(fn($v) => (int)$v)->all();

        return view('administracao.rh.usuario.edit', compact('usuario', 'situacao', 'perfil', 'funcoes', 'selecionadas'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->flash();

            if (empty($request->id)) {

                $matriculaExistente = Usuario::obterPorMatricula($request->matricula);
                if (!empty($matriculaExistente) == $request->matricula) {
                    return redirect()->back()->with('error', 'Esse passaporte já está sendo utilizado(a).');
                }

                $obj = Usuario::create([
                    'nome' => Str::upper($request->nome),
                    'matricula' => $request->matricula,
                    'data_admissao' => date('Y-m-d', DateTime::createFromFormat('d/m/Y', $request->data_admissao)->getTimestamp()),
                    'situacao_id' => $request->situacao,
                    'perfil_id' => $request->perfil
                ]);
                LogCadastro::inserir("USUÁRIO", "INSERIR", "NOME: " . $obj->nome . ", Inserindo", $obj->id);
            } else {
                $obj  = Usuario::find($request->id);
                $obj->update([
                    'nome' => Str::upper($request->nome),
                    'matricula' => $request->matricula,
                    'data_admissao' => date('Y-m-d', DateTime::createFromFormat('d/m/Y', $request->data_admissao)->getTimestamp()),
                    'situacao_id' => $request->situacao,
                    'perfil_id' => $request->perfil
                ]);
                LogCadastro::inserir("USUÁRIO", "ATUALIZAR", "NOME: " . $obj->nome . ", Atualizando", $obj->id);
            }

            if (!empty($request->senha)) {
                $obj->senha = Hash::make($request->senha);
            }

            // --- Funções múltiplas ---
            $ids = collect($request->input('funcoes', []))
                ->filter(fn($v) => $v !== '' && $v !== null)
                ->map(fn($v) => (int)$v)
                ->unique()
                ->values()
                ->all();

            if (empty($ids)) {
                // nada marcado => sem funções
                $obj->funcoes()->sync([]);
            } else {
                $now = now();
                $payload = [];
                foreach ($ids as $fid) {
                    $payload[$fid] = ['data_atribuicao' => $now];
                }
                $obj->funcoes()->sync($payload);
            }
            // -------------------------

            DB::commit();
            return redirect()->back()->with('success', 'O usuário foi atualizado com sucesso. ');
        } catch (\Exception $e) {
            DB::rollBack();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o usuário. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = Usuario::find($id);
            $banco->funcoes()->detach();
            LogCadastro::inserir("USUÁRIO", "EXCLUIR", "NOME: {$banco->nome}, Excluindo", $banco->id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'O Usuário foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir o usuário');
        }
    }
}
