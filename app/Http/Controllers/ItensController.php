<?php

namespace App\Http\Controllers;

use App\Models\Itens;
use App\Utils;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class ItensController extends Controller
{
    public function index(Request $request)
    {
        $request->all();


        $listItens = Itens::obterPorFiltros($request);
        $listItens = Utils::arrayPaginator($listItens, route('administracao.estoque.itens.index'), $request, 4);

        return view('administracao.estoque.itens.index', compact('listItens'));
    }
}
