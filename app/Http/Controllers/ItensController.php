<?php

namespace App\Http\Controllers;

use App\Models\Itens;
use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class ItensController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listItens = Itens::obterPorFiltros($request);
        $listItens = Utils::arrayPaginator($listItens, route('administracao.estoque.itens.index'), $request, 4);
        return view('administracao.estoque.itens.index', compact('listItens'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $itens = new Itens();
        } else {
            $itens = Itens::find($id);
        }
        return view('administracao.estoque.itens.edit', compact('itens'));
    }

    public function store(Request $request)
    {
        try {
            $request->all();
            DB::beginTransaction();
            if (empty($request->id)) {
                $itens = Itens::create([
                    'id' => Utils::getSequence(Itens::$sequence),
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("ITEM", "INSERIR", "NOME: " . $itens->nome . ", Inserindo", $itens->id);
            } else {
                $itens = Itens::find($request->id);
                $itens->update([
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("ITEM", "ATUALIZAR", "NOME: " . $itens->nome . ", Atualizando", $itens->id);
            }
            DB::commit();
            return redirect()->route('administracao.estoque.itens.index')->with('success', 'O item do foi salvo com sucesso.');
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
            $banco = Itens::find($id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'O item do foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir o item');
        }
    }
}
