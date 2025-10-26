<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaginationHelper
{
    /**
     * Pagina uma query do Eloquent de forma eficiente
     *
     * @param Builder $query Query do Eloquent
     * @param Request $request Request atual
     * @param int $perPage Itens por página (padrão: 15)
     * @param int $maxPerPage Máximo de itens por página (padrão: 100)
     * @return LengthAwarePaginator
     */
    public static function paginateQuery(
        Builder $query,
        Request $request,
        int $perPage = 15,
        int $maxPerPage = 100
    ): LengthAwarePaginator {
        // Limita o número máximo de itens por página para evitar sobrecarga
        $requestedPerPage = max(1, min($maxPerPage, (int) $request->input('per_page', $perPage)));

        return $query->paginate($requestedPerPage)
            ->appends($request->query());
    }

    /**
     * Pagina dados já carregados de forma eficiente com cache
     * Usar apenas quando não for possível paginar direto na query
     *
     * @param mixed $data Dados para paginar
     * @param Request $request Request atual
     * @param string $route Rota para links de paginação
     * @param int $perPage Itens por página
     * @param string $pageName Nome do parâmetro de página
     * @return LengthAwarePaginator
     */
    public static function paginateCollection(
        $data,
        Request $request,
        string $route,
        int $perPage = 15,
        string $pageName = 'page'
    ): LengthAwarePaginator {
        // Usa a implementação existente, mas com validação
        $perPage = max(1, min(100, $perPage)); // Limita entre 1 e 100

        return \App\Utils::arrayPaginator($data, $route, $request, $perPage, $pageName);
    }

    /**
     * Configurações otimizadas para diferentes tipos de listagem
     */
    public static function getOptimizedConfig(string $type): array
    {
        return match ($type) {
            'fila_vendas' => [
                'per_page' => 15,
                'max_per_page' => 50,
                'eager_load' => ['itens.produto', 'organizacao', 'usuario']
            ],
            'solicitacoes' => [
                'per_page' => 20,
                'max_per_page' => 100,
                'eager_load' => []
            ],
            'lancamentos' => [
                'per_page' => 25,
                'max_per_page' => 100,
                'eager_load' => ['item', 'bauOrigem', 'bauDestino', 'usuario']
            ],
            'historico_series' => [
                'per_page' => 30,
                'max_per_page' => 120,
                'eager_load' => []
            ],
            'ranking' => [
                'per_page' => 10,
                'max_per_page' => 50,
                'eager_load' => []
            ],
            'anomalias' => [
                'per_page' => 10,
                'max_per_page' => 25,
                'eager_load' => []
            ],
            default => [
                'per_page' => 15,
                'max_per_page' => 50,
                'eager_load' => []
            ]
        };
    }

    /**
     * Aplica paginação com configuração otimizada
     */
    public static function paginateWithConfig(
        Builder $query,
        Request $request,
        string $type
    ): LengthAwarePaginator {
        $config = self::getOptimizedConfig($type);

        // Aplica eager loading se configurado
        if (!empty($config['eager_load'])) {
            $query->with($config['eager_load']);
        }

        return self::paginateQuery(
            $query,
            $request,
            $config['per_page'],
            $config['max_per_page']
        );
    }

    /**
     * Gera metadados de paginação para APIs
     */
    public static function getPaginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'last_page' => $paginator->lastPage(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_more_pages' => $paginator->hasMorePages(),
        ];
    }

    /**
     * Valida parâmetros de paginação da request
     */
    public static function validatePaginationParams(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, min(100, (int) $request->input('per_page', 15)));

        return compact('page', 'perPage');
    }
}
