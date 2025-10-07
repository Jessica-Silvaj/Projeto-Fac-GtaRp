<?php

namespace App\Http\Controllers;

use App\Models\Baus;
use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class BausController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listBaus = Baus::obterPorFiltros($request);
        $listBaus = Utils::arrayPaginator($listBaus, route('administracao.estoque.baus.index'), $request, 10);
        return view('administracao.estoque.baus.index', compact('listBaus'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $baus = new Baus();
        } else {
            $baus = Baus::find($id);
        }
        return view('administracao.estoque.baus.edit', compact('baus'));
    }

    public function store(Request $request)
    {
        try {
            $request->all();
            DB::beginTransaction();
            if (empty($request->id)) {
                $baus = Baus::create([
                    'id' => Utils::getSequence(Baus::$sequence),
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("BAUS", "INSERIR", "NOME: " . $baus->nome . ", Inserindo", $baus->id);
            } else {
                $baus = Baus::find($request->id);
                $baus->update([
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("BAUS", "ATUALIZAR", "NOME: " . $baus->nome . ", Atualizando", $baus->id);
            }
            DB::commit();
            return redirect()->route('administracao.estoque.baus.index')->with('success', 'O baú foi salvo com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o Baú');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = baus::find($id);
            LogCadastro::inserir("BAUS", "EXCLUIR", "NOME: {$banco->nome}, Excluindo", $banco->id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'O baú foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir o baú');
        }
    }
}
