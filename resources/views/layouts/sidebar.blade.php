<nav class="pcoded-navbar">
    <div class="sidebar_toggle"><a href="#"><i class="icon-close icons"></i></a></div>
    <div class="pcoded-inner-navbar main-menu">
        <div class="pcoded-navigation-label">Administração</div>
        <ul class="pcoded-item pcoded-left-item">
            <li class="pcoded-hasmenu {{ request()->routeIs('administracao.rh.*') ? 'active pcoded-trigger' : '' }}">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-user"></i><b>RH</b></span>
                    <span class="pcoded-mtext">Recursos Humanos</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="breadcrumb.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Usuários</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                    <li class="{{ request()->routeIs('administracao.rh.perfil') ? 'active' : '' }} ">
                        <a href="{{ route('administracao.rh.perfil.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Perfil</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                    <li class="{{ request()->routeIs('administracao.rh.situacao') ? 'active' : '' }}">
                        <a href="{{ route('administracao.rh.situacao.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Situação</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                    <li class=" {{ request()->routeIs('administracao.rh.funcao') ? 'active' : '' }}">
                        <a href="{{ route('administracao.rh.funcao.index') }} " class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Funções</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                </ul>
            </li>

            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-settings"></i><b>SI</b></span>
                    <span class="pcoded-mtext">Sistema</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="breadcrumb.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Permissões</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>

            <li
                class="pcoded-hasmenu {{ request()->routeIs('administracao.estoque.*') ? 'active pcoded-trigger' : '' }}">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-package"></i><b>ES</b></span>
                    <span class="pcoded-mtext">Estoque</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class="{{ request()->routeIs('administracao.estoque.itens') ? 'active' : '' }} ">
                        <a href="{{ route('administracao.estoque.itens.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Itens</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                    <li class="{{ request()->routeIs('administracao.estoque.baus') ? 'active' : '' }} ">
                        <a href="{{ route('administracao.estoque.baus.index') }}" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Baús</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="pcoded-hasmenu">
                <a href="javascript:void(0)" class="waves-effect waves-dark">
                    <span class="pcoded-micon"><i class="ti-truck"></i><b>FB</b></span>
                    <span class="pcoded-mtext">Fabricação</span>
                    <span class="pcoded-mcaret"></span>
                </a>
                <ul class="pcoded-submenu">
                    <li class=" ">
                        <a href="breadcrumb.html" class="waves-effect waves-dark">
                            <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                            <span class="pcoded-mtext">Produto</span>
                            <span class="pcoded-mcaret"></span>
                        </a>
                    </li>

                </ul>
            </li>
        </ul>


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
    </div>
</nav>
