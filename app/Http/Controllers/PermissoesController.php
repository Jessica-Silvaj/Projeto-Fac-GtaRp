<?php

namespace App\Http\Controllers;

use App\Models\Funcao;
use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Permissoes;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PermissoesController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listPermissoes = Permissoes::obterPorFiltros($request);
        $listPermissoes = Utils::arrayPaginator($listPermissoes, route('administracao.sistema.permissoes.index'), $request, 10);
        return view('administracao.sistema.permissoes.index', compact('listPermissoes'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $permissoes = new Permissoes();
            $permissoes->setRelation('funcoes', collect());
        } else {
            $permissoes = Permissoes::find($id);
        }

        $funcoes = Funcao::obterTodos();

        $selecionadas = collect(old('funcoes', $permissoes->funcoes->pluck('id')->all()))->map(fn($v) => (int)$v)->all();
        return view('administracao.sistema.permissoes.edit', compact('permissoes', 'selecionadas', 'funcoes'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->flash();

            if (empty($request->id)) {
                $obj = Permissoes::create([
                    'id' => Utils::getSequence(Permissoes::$sequence),
                    'nome' => $request->nome,
                    'descricao' => Str::upper($request->descricao),
                    'ativo' => $request->ativo,
                ]);

                LogCadastro::inserir("PERMISSÕES", "INSERIR", "NOME: " . $obj->nome . ", Inserindo", $obj->id);
            } else {
                $obj  = Permissoes::find($request->id);
                $obj->update([
                    'nome' => $request->nome,
                    'descricao' => Str::upper($request->descricao),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("PERMISSÕES", "ATUALIZAR", "NOME: " . $obj->nome . ", Atualizando", $obj->id);
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
            return redirect()->back()->with('success', 'A permissões foi atualizado com sucesso. ');
        } catch (\Exception $e) {
            DB::rollBack();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um error ao atualizar o permissões. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = Permissoes::find($id);
            $banco->funcoes()->detach();
            LogCadastro::inserir("PERMISSÕES", "EXCLUIR", "NOME: {$banco->nome}, Excluindo", $banco->id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'A Permissões foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir o permissões');
        }
    }
}
