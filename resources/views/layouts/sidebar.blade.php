<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        @php
            /**
             * Verifica se o usuário tem qualquer permissão do array usando o gate 'acesso'
             */
            if (!function_exists('hasAnyAcesso')) {
                function hasAnyAcesso(array $perms): bool
                {
                    $user = auth()->user();
                    if (!$user) {
                        return false;
                    }
                    foreach ($perms as $p) {
                        if ($user->can('acesso', $p)) {
                            return true;
                        }
                    }
                    return false;
                }
            }

            /**
             * Verifica se a rota está ativa (suporte a array e string)
             */
            if (!function_exists('isActiveRoute')) {
                function isActiveRoute($routes): string
                {
                    if (is_array($routes)) {
                        foreach ($routes as $route) {
                            if (request()->routeIs($route)) {
                                return 'active';
                            }
                        }
                        return '';
                    }
                    return request()->routeIs($routes) ? 'active' : '';
                }
            }

            if (!function_exists('getSubmenuActiveClass')) {
                function getSubmenuActiveClass(string $pattern): string
                {
                    return request()->routeIs($pattern) ? 'active pcoded-trigger' : '';
                }
            }
        @endphp

        {{-- Seção Administração --}}
        @php
            $adminPerms = [
                'administracao.rh.usuario.index',
                'administracao.rh.perfil.index',
                'administracao.rh.situacao.index',
                'administracao.rh.funcao.index',
                'administracao.rh.frequencia.index',
                'administracao.sistema.permissoes.index',
                'administracao.sistema.configuracao.anomalia.edit',
                'administracao.estoque.itens.index',
                'administracao.estoque.baus.index',
                'administracao.fabricacao.produtos.index',
                'administracao.fabricacao.organizacao.index',
            ];

            $rhPerms = [
                'administracao.rh.usuario.index',
                'administracao.rh.perfil.index',
                'administracao.rh.situacao.index',
                'administracao.rh.funcao.index',
                'administracao.rh.frequencia.index',
            ];

            $sistemaPerms = [
                'administracao.sistema.permissoes.index',
                'administracao.sistema.configuracao.anomalia.edit',
            ];

            $estoquePerms = ['administracao.estoque.itens.index', 'administracao.estoque.baus.index'];

            $fabricacaoPerms = [
                'administracao.fabricacao.produtos.index',
                'administracao.fabricacao.organizacao.index',
            ];
        @endphp

        @if (hasAnyAcesso($adminPerms))
            <div class="pcoded-navigation-label">Administração</div>
            <ul class="pcoded-item pcoded-left-item">
                {{-- Submenu RH --}}
                @if (hasAnyAcesso($rhPerms))
                    <li class="pcoded-hasmenu {{ getSubmenuActiveClass('administracao.rh.*') }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-user"></i><b>RH</b></span>
                            <span class="pcoded-mtext">Recursos Humanos</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.rh.usuario.index')
                                <li class="{{ isActiveRoute('administracao.rh.usuario') }}">
                                    <a href="{{ route('administracao.rh.usuario.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>U</b></span>
                                        <span class="pcoded-mtext">Usuários</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.rh.perfil.index')
                                <li class="{{ isActiveRoute('administracao.rh.perfil') }}">
                                    <a href="{{ route('administracao.rh.perfil.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-id-badge"></i><b>P</b></span>
                                        <span class="pcoded-mtext">Perfil</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.rh.situacao.index')
                                <li class="{{ isActiveRoute('administracao.rh.situacao') }}">
                                    <a href="{{ route('administracao.rh.situacao.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-flag"></i><b>S</b></span>
                                        <span class="pcoded-mtext">Situação</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.rh.funcao.index')
                                <li class="{{ isActiveRoute('administracao.rh.funcao') }}">
                                    <a href="{{ route('administracao.rh.funcao.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-crown"></i><b>F</b></span>
                                        <span class="pcoded-mtext">Funções</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.rh.frequencia.index')
                                <li class="{{ isActiveRoute('administracao.rh.frequencia') }}">
                                    <a href="{{ route('administracao.rh.frequencia.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-calendar"></i><b>FR</b></span>
                                        <span class="pcoded-mtext">Frequência</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                {{-- Submenu Sistema --}}
                @if (hasAnyAcesso($sistemaPerms))
                    <li class="pcoded-hasmenu {{ getSubmenuActiveClass('administracao.sistema.*') }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-settings"></i><b>SI</b></span>
                            <span class="pcoded-mtext">Sistema</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.sistema.permissoes.index')
                                <li class="{{ isActiveRoute('administracao.sistema.permissoes') }}">
                                    <a href="{{ route('administracao.sistema.permissoes.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-lock"></i><b>P</b></span>
                                        <span class="pcoded-mtext">Permissões</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.sistema.configuracao.anomalia.edit')
                                <li class="{{ isActiveRoute('administracao.sistema.configuracao.anomalia.edit') }}">
                                    <a href="{{ route('administracao.sistema.configuracao.anomalia.edit') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-ruler-pencil"></i><b>A</b></span>
                                        <span class="pcoded-mtext">Config. Anomalias</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                {{-- Submenu Estoque --}}
                @if (hasAnyAcesso($estoquePerms))
                    <li class="pcoded-hasmenu {{ getSubmenuActiveClass('administracao.estoque.*') }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-package"></i><b>ES</b></span>
                            <span class="pcoded-mtext">Estoque</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.estoque.itens.index')
                                <li class="{{ isActiveRoute('administracao.estoque.itens') }}">
                                    <a href="{{ route('administracao.estoque.itens.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-package"></i><b>I</b></span>
                                        <span class="pcoded-mtext">Itens</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.estoque.baus.index')
                                <li class="{{ isActiveRoute('administracao.estoque.baus') }}">
                                    <a href="{{ route('administracao.estoque.baus.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-archive"></i><b>B</b></span>
                                        <span class="pcoded-mtext">Baús</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif

                {{-- Submenu Fabricação --}}
                @if (hasAnyAcesso($fabricacaoPerms))
                    <li class="pcoded-hasmenu {{ getSubmenuActiveClass('administracao.fabricacao.*') }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-truck"></i><b>FB</b></span>
                            <span class="pcoded-mtext">Fabricação</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.fabricacao.produtos.index')
                                <li class="{{ isActiveRoute('administracao.fabricacao.produtos') }}">
                                    <a href="{{ route('administracao.fabricacao.produtos.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-package"></i><b>P</b></span>
                                        <span class="pcoded-mtext">Produtos</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                            @can('acesso', 'administracao.fabricacao.organizacao.index')
                                <li class="{{ isActiveRoute('administracao.fabricacao.organizacao') }}">
                                    <a href="{{ route('administracao.fabricacao.organizacao.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>O</b></span>
                                        <span class="pcoded-mtext">Organizações</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endif
            </ul>
        @endif

        {{-- Seção Navegação --}}
        <div class="pcoded-navigation-label">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="{{ isActiveRoute('dashboard') }}">
                <a href="{{ route('dashboard') }}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Painel</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        {{-- Seção Controle Baú --}}
        @php
            $bauPerms = [
                'bau.lancamentos.index',
                'bau.lancamentos.historico',
                'bau.lancamentos.estoque',
                'bau.lancamentos.solicitacoes.index',
                'bau.lancamentos.anomalias',
            ];
        @endphp

        @if (hasAnyAcesso($bauPerms))
            <div class="pcoded-navigation-label">Controle Baú</div>
            <ul class="pcoded-item pcoded-left-item">
                @can('acesso', 'bau.lancamentos.index')
                    <li class="{{ isActiveRoute(['bau.lancamentos.index', 'bau.lancamentos.edit']) }}">
                        <a href="{{ route('bau.lancamentos.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-exchange-vertical"></i><b>L</b></span>
                            <span class="pcoded-mtext">Lançamentos</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.solicitacoes.index')
                    <li class="{{ isActiveRoute('bau.lancamentos.solicitacoes.*') }}">
                        <a href="{{ route('bau.lancamentos.solicitacoes.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-clipboard"></i><b>S</b></span>
                            <span class="pcoded-mtext">Solicitações</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.historico')
                    <li class="{{ isActiveRoute('bau.lancamentos.historico') }}">
                        <a href="{{ route('bau.lancamentos.historico') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-bar-chart"></i><b>H</b></span>
                            <span class="pcoded-mtext">Histórico</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.estoque')
                    <li class="{{ isActiveRoute('bau.lancamentos.estoque') }}">
                        <a href="{{ route('bau.lancamentos.estoque') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-archive"></i><b>E</b></span>
                            <span class="pcoded-mtext">Estoque Total</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.anomalias')
                    <li class="{{ isActiveRoute('bau.lancamentos.anomalias') }}">
                        <a href="{{ route('bau.lancamentos.anomalias') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-alert"></i><b>A</b></span>
                            <span class="pcoded-mtext">Anomalias</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
            </ul>
        @endif

        {{-- Seção Vendas --}}
        @php
            $vendasPerms = ['venda.fila.index', 'venda.fila.create', 'venda.fila.historico'];
        @endphp

        @if (hasAnyAcesso($vendasPerms))
            <div class="pcoded-navigation-label">Controle Vendas</div>
            <ul class="pcoded-item pcoded-left-item">
                @can('acesso', 'venda.fila.index')
                    <li class="{{ isActiveRoute(['venda.fila.index', 'venda.fila.create', 'venda.fila.edit']) }}">
                        <a href="{{ route('venda.fila.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-shopping-cart"></i><b>F</b></span>
                            <span class="pcoded-mtext">Fila de Vendas</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'venda.fila.historico')
                    <li class="{{ isActiveRoute('venda.fila.historico') }}">
                        <a href="{{ route('venda.fila.historico') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-bar-chart-alt"></i><b>HV</b></span>
                            <span class="pcoded-mtext">Histórico Vendas</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
            </ul>
        @endif
    </div>
</nav>
