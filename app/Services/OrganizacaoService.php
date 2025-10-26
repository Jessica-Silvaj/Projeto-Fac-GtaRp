<?php

namespace App\Services;

use App\Models\Organizacao;
use App\Services\Contracts\OrganizacaoServiceInterface;
use App\Utils;
use Illuminate\Http\Request;

class OrganizacaoService implements OrganizacaoServiceInterface
{
    public function listar(Request $request)
    {
        $lista = Organizacao::obterPorFiltros($request);
        return Utils::arrayPaginator($lista, route('administracao.fabricacao.organizacao.index'), $request, 10);
    }

    public function dadosEdicao(int $id): array
    {
        $organizacao = $id ? Organizacao::find($id) : null;
        return ['organizacao' => $organizacao];
    }

    public function salvar(array $data): void
    {
        if (array_key_exists('ativo', $data)) {
            $data['ativo'] = (int) filter_var($data['ativo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }
        if (!empty($data['id'])) {
            $entity = Organizacao::find($data['id']);
            if ($entity) {
                $entity->update($data);
                return;
            }
        }
        Organizacao::create($data);
    }

    public function excluir(int $id): void
    {
        Organizacao::destroy($id);
    }
}
