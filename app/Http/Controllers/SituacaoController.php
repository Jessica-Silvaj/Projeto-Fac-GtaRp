<?php

namespace App\Http\Controllers;

use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Situacao;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class SituacaoController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listSituacao = Situacao::obterPorFiltros($request);
        $listSituacao = Utils::arrayPaginator($listSituacao, route('administracao.rh.situacao.index'), $request, 10);
        return view('administracao.rh.situacao.index', compact('listSituacao'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $situacao = new Situacao();
        } else {
            $situacao = Situacao::find($id);
        }
        return view('administracao.rh.situacao.edit', compact('situacao'));
    }

    public function store(Request $request)
    {
        try {
            $request->all();
            DB::beginTransaction();
            if (empty($request->id)) {
                $situacao = Situacao::create([
                    'id' => Utils::getSequence(Situacao::$sequence),
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("SITUAÇÃO", "INSERIR", "NOME: " . $situacao->nome . ", Inserindo", $situacao->id);
            } else {
                $situacao = Situacao::find($request->id);
                $situacao->update([
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("SITUAÇÃO", "ATUALIZAR", "NOME: " . $situacao->nome . ", Atualizando", $situacao->id);
            }
            DB::commit();
            return redirect()->route('administracao.rh.situacao.index')->with('success', 'A situação foi salva com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o item');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = Situacao::find($id);
            LogCadastro::inserir("SITUAÇÃO", "EXCLUIR", "NOME: {$banco->nome}, Excluindo", $banco->id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'A situação do foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir a situação');
        }
    }
}
