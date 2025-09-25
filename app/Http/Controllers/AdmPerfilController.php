<?php

namespace App\Http\Controllers;

use App\Models\LogCadastro;
use App\Models\LogExcecao;
use App\Models\Perfil;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


class AdmPerfilController extends Controller
{
    public function index(Request $request)
    {
        $request->all();
        $listPerfil = Perfil::obterPorFiltros($request);
        $listPerfil = Utils::arrayPaginator($listPerfil, route('administracao.rh.perfil.index'), $request, 10);
        return view('administracao.rh.perfil.index', compact('listPerfil'));
    }

    public function edit($id = 0)
    {
        if (empty($id)) {
            $perfil = new Perfil();
        } else {
            $perfil = Perfil::find($id);
        }
        return view('administracao.rh.perfil.edit', compact('perfil'));
    }

    public function store(Request $request)
    {
        try {
            $request->all();
            DB::beginTransaction();
            if (empty($request->id)) {
                $perfil = Perfil::create([
                    'id' => Utils::getSequence(Perfil::$sequence),
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("PERFIL", "INSERIR", "NOME: " . $perfil->nome . ", Inserindo", $perfil->id);
            } else {
                $perfil = Perfil::find($request->id);
                $perfil->update([
                    'nome' => Str::upper($request->nome),
                    'ativo' => $request->ativo,
                ]);
                LogCadastro::inserir("PERFIL", "ATUALIZAR", "NOME: " . $perfil->nome . ", Atualizando", $perfil->id);
            }
            DB::commit();
            return redirect()->route('administracao.rh.perfil.index')->with('success', 'O perfil foi salvo com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o perfil');
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $banco = Perfil::find($id);
            $banco->delete();
            DB::commit();
            return redirect()->back()->with('success', 'O Perfil foi excluído com sucesso.');
        } catch (\Exception $e) {
            DB::rollback();
            LogExcecao::inserirExcessao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao exluir perfil');
        }
    }
}
