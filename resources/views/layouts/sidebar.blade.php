<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        @can('acesso', ['administracao.rh.usuario.index', 'administracao.rh.perfil.index',
            'administracao.rh.situacao.index', 'administracao.rh.funcao.index', 'administracao.sistema.permissoes.index'])
            <div class="pcoded-navigation-label">Administração</div>

            <ul class="pcoded-item pcoded-left-item">
                @can('acesso', ['administracao.rh.usuario.index', 'administracao.rh.perfil.index',
                    'administracao.rh.situacao.index', 'administracao.rh.funcao.index'])
                    <li class="pcoded-hasmenu {{ request()->routeIs('administracao.rh.*') ? 'active pcoded-trigger' : '' }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-user"></i><b>RH</b></span>
                            <span class="pcoded-mtext">Recursos Humanos</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.rh.usuario.index')
                                <li class=" {{ request()->routeIs('administracao.rh.usuario') ? 'active' : '' }}">
                                    <a href="{{ route('administracao.rh.usuario.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Usuários</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan

                            @can('acesso', 'administracao.rh.perfil.index')
                                <li class="{{ request()->routeIs('administracao.rh.perfil') ? 'active' : '' }} ">
                                    <a href="{{ route('administracao.rh.perfil.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Perfil</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan

                            @can('acesso', 'administracao.rh.situacao.index')
                                <li class="{{ request()->routeIs('administracao.rh.situacao') ? 'active' : '' }}">
                                    <a href="{{ route('administracao.rh.situacao.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Situação</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan

                            @can('acesso', 'administracao.rh.funcao.index')
                                <li class=" {{ request()->routeIs('administracao.rh.funcao') ? 'active' : '' }}">
                                    <a href="{{ route('administracao.rh.funcao.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Funções</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('acesso', 'administracao.sistema.permissoes.index')
                    <li
                        class="pcoded-hasmenu {{ request()->routeIs('administracao.sistema.*') ? 'active pcoded-trigger' : '' }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-settings"></i><b>SI</b></span>
                            <span class="pcoded-mtext">Sistema</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu ">
                            @can('acesso', 'administracao.sistema.permissoes.index')
                                <li class=" {{ request()->routeIs('administracao.sistema.permissoes') ? 'active' : '' }} ">
                                    <a href="{{ route('administracao.sistema.permissoes.index') }}"
                                        class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Permissões</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                @can('acesso', ['administracao.estoque.itens.index', 'administracao.estoque.baus.index'])
                    <li
                        class="pcoded-hasmenu {{ request()->routeIs('administracao.estoque.*') ? 'active pcoded-trigger' : '' }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-package"></i><b>ES</b></span>
                            <span class="pcoded-mtext">Estoque</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            @can('acesso', 'administracao.estoque.itens.index')
                                <li class="{{ request()->routeIs('administracao.estoque.itens') ? 'active' : '' }} ">
                                    <a href="{{ route('administracao.estoque.itens.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Itens</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan

                            @can('acesso', 'administracao.estoque.baus.index')
                                <li class="{{ request()->routeIs('administracao.estoque.baus') ? 'active' : '' }} ">
                                    <a href="{{ route('administracao.estoque.baus.index') }}" class="waves-effect waves-dark">
                                        <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                        <span class="pcoded-mtext">Baús</span>
                                        <span class="pcoded-mcaret"></span>
                                    </a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan
                @can('acesso', 'administracao.fabricacao.produtos.index')
                    <li
                        class="pcoded-hasmenu {{ request()->routeIs('administracao.fabricacao.*') ? 'active pcoded-trigger' : '' }}">
                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-truck"></i><b>FB</b></span>
                            <span class="pcoded-mtext">Fabricão</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                        <ul class="pcoded-submenu">
                            <li class="{{ request()->routeIs('administracao.fabricacao.produtos') ? 'active' : '' }} ">
                                <a href="{{ route('administracao.fabricacao.produtos.index') }}"
                                    class="waves-effect waves-dark">
                                    <span class="pcoded-micon"><i class="ti-briefcase"></i><b>CB</b></span>
                                    <span class="pcoded-mtext">Produtos</span>
                                    <span class="pcoded-mcaret"></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
            </ul>
        @endcan

        <div class="pcoded-navigation-label">Navegação</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                    <span class="pcoded-mtext">Painel</span>
                    <span class="pcoded-mcaret"></span>
                </a>
            </li>
        </ul>

        @can('acesso', ['bau.lancamentos.index', 'bau.lancamentos.historico'])
            <div class="pcoded-navigation-label">Controle Baú</div>
            <ul class="pcoded-item pcoded-left-item">
                    @can('acesso', 'bau.lancamentos.index')
                        <li class=" {{ (request()->routeIs('bau.lancamentos.index') || request()->routeIs('bau.lancamentos.edit')) ? 'active' : '' }}">
                        <a href="{{ route('bau.lancamentos.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-exchange-vertical"></i><b>CB</b></span>
                            <span class="pcoded-mtext">Lançamentos</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.historico')
                    <li class=" {{ request()->routeIs('bau.lancamentos.historico') ? 'active' : '' }}">
                        <a href="{{ route('bau.lancamentos.historico') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-bar-chart"></i><b>CB</b></span>
                            <span class="pcoded-mtext">Historico</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                @endcan
            </ul>
        @endcan
    </div>
    </div>

</nav>
