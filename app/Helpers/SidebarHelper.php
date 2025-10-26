<?php

namespace App\Helpers;

class SidebarHelper
{
    /**
     * Verifica se o usuário tem acesso a qualquer permissão de um array
     *
     * @param array $permissions
     * @return bool
     */
    public static function hasAnyAccess(array $permissions): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (\Illuminate\Support\Facades\Gate::forUser($user)->allows('acesso', $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se a rota atual está ativa
     *
     * @param string|array $routes
     * @return bool
     */
    public static function isRouteActive($routes): bool
    {
        if (is_string($routes)) {
            return request()->routeIs($routes);
        }

        if (is_array($routes)) {
            foreach ($routes as $route) {
                if (request()->routeIs($route)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gera a classe CSS para item ativo
     *
     * @param string|array $routes
     * @return string
     */
    public static function getActiveClass($routes): string
    {
        return self::isRouteActive($routes) ? 'active' : '';
    }

    /**
     * Gera a classe CSS para submenu ativo
     *
     * @param string $routePattern
     * @return string
     */
    public static function getSubmenuActiveClass(string $routePattern): string
    {
        return request()->routeIs($routePattern) ? 'active pcoded-trigger' : '';
    }

    /**
     * Obtém a configuração da sidebar
     *
     * @return array
     */
    public static function getConfig(): array
    {
        return config('sidebar.sections', []);
    }

    /**
     * Verifica se uma seção deve ser exibida
     *
     * @param array $section
     * @return bool
     */
    public static function shouldShowSection(array $section): bool
    {
        // Se sempre visível, mostra
        if (isset($section['always_visible']) && $section['always_visible']) {
            return true;
        }

        // Se tem permissões definidas, verifica acesso
        if (isset($section['permissions'])) {
            return self::hasAnyAccess($section['permissions']);
        }

        // Por padrão, mostra a seção
        return true;
    }
}
