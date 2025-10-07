<?php

namespace App\Http\Controllers;

use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Funcao;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class FuncaoController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listFuncao = Funcao::obterPorFiltros($request);
        $listFuncao = Utils::arrayPaginator($listFuncao, route('administracao.rh.funcao.index'), $request, 10);
        return view('administracao.rh.funcao.index', compact('listFuncao'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $funcao = new Funcao();
        } else {
            $funcao = Funcao::find($id);
        }
        return view('administracao.rh.funcao.edit', compact('funcao'));
    }

    public function store(Request $request)
    {
        try {
            $request->all();
            DB::beginTransaction();
            if (empty($request->id)) {
                $funcao = Funcao::create([
                    'id' => Utils::getSequence(Funcao::$sequence),
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("FUNÇÃO", "INSERIR", "NOME: " . $funcao->nome . ", Inserindo", $funcao->id);
            } else {
                $funcao = Funcao::find($request->id);
                $funcao->update([
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("FUNÇÃO", "ATUALIZAR", "NOME: " . $funcao->nome . ", Atualizando", $funcao->id);
            }
            DB::commit();
            return redirect()->route('administracao.rh.funcao.index')->with('success', 'A função foi salva com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar a função');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = Funcao::find($id);
            LogCadastro::inserir("FUNÇÃO", "EXCLUIR", "NOME: {$banco->nome}, Excluindo", $banco->id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'A função foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir a função');
        }
    }
}
