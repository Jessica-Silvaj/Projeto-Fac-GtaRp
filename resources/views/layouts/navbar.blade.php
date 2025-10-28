<nav class="navbar header-navbar pcoded-header">
    <div class="navbar-wrapper">
        <div class="navbar-logo">
            <a class="mobile-menu waves-effect waves-light" id="mobile-collapse" href="#!">
                <i class="ti-menu"></i>
            </a>
            <div class="mobile-search waves-effect waves-light">
                <div class="header-search">
                    <div class="main-search morphsearch-search">
                        <div class="input-group">
                            <span class="input-group-prepend search-close"><i
                                    class="ti-close input-group-text"></i></span>
                            <input type="text" class="form-control" placeholder="Enter Keyword">
                            <span class="input-group-append search-btn"><i
                                    class="ti-search input-group-text"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('dashboard') }}">
                <img class="img-fluid" src="{{ asset('assets/images/mrk-dourada.png') }} " alt="Theme-Logo"
                    style="max-height:100px; height:auto; width:160px; margin-left: 10px">
            </a>
            <a class="mobile-options waves-effect waves-light">
                <i class="ti-more"></i>
            </a>
        </div>
        <div class="navbar-container container-fluid">
            <ul class="nav-left">
                <li>
                    <div class="sidebar_toggle"><a href="javascript:void(0)"><i class="ti-menu"></i></a></div>
                </li>
                <li>
                    <a href="#!" onclick="javascript:toggleFullScreen()" class="waves-effect waves-light">
                        <i class="ti-fullscreen"></i>
                    </a>
                </li>
            </ul>
            <ul class="nav-right">
                @can('acesso', 'bau.lancamentos.anomalias')
                    <li class="header-notification dropdown" id="estoque-alerta-wrapper">
                        <a href="#!" class="waves-effect waves-light">
                            <i class="ti-pulse" style="font-size:18px;"></i>
                            <span class="badge bg-c-red badge-alerta" style="size:5px;" id="estoque-alerta-badge">0</span>
                        </a>
                        <ul class="show-notification" id="estoque-alerta-dropdown">
                            <li>
                                <h6>Estoques crí­ticos</h6>
                                <label class="label label-warning">Monitoramento</label>
                            </li>
                            <li class="text-center text-muted py-2" id="estoque-alerta-empty">Carregando...</li>
                            <li class="text-center py-2" data-role="panel-link">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('bau.lancamentos.anomalias') }}">
                                    Abrir painel
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
                @can('acesso', 'bau.lancamentos.solicitacoes.index')
                    <li class="header-notification dropdown" id="solicitacao-alerta-wrapper">
                        <a href="#!" class="waves-effect waves-light">
                            <i class="ti-clipboard" style="font-size:18px;"></i>
                            <span class="badge bg-c-red badge-alerta" id="solicitacao-alerta-badge">0</span>
                        </a>
                        <ul class="show-notification" id="solicitacao-alerta-dropdown">
                            <li>
                                <h6>Solicitações pendentes</h6>
                                <label class="label label-warning">Revisão</label>
                            </li>
                            <li class="text-center text-muted py-2" id="solicitacao-alerta-empty">Carregando...</li>
                            <li class="text-center py-2" data-role="solicitacao-link">
                                <a class="btn btn-sm btn-outline-secondary"
                                    href="{{ route('bau.lancamentos.solicitacoes.index') }}">
                                    Abrir solicitações
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan
                @can('acesso', 'venda.fila.index')
                    <li class="header-notification dropdown" id="vendas-pendentes-wrapper">
                        <a href="#!" class="waves-effect waves-light">
                            <i class="ti-shopping-cart" style="font-size:18px;"></i>
                            <span class="badge bg-c-blue badge-alerta" id="vendas-pendentes-badge">0</span>
                        </a>
                        <ul class="show-notification" id="vendas-pendentes-dropdown">
                            <li>
                                <h6>Vendas Pendentes</h6>
                                <label class="label label-primary">Processamento</label>
                            </li>
                            <li class="text-center text-muted py-2" id="vendas-pendentes-empty">Carregando...</li>
                            <li class="text-center py-2" data-role="vendas-pendentes-link">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('venda.fila.index') }}">
                                    Abrir fila de vendas
                                </a>
                            </li>
                        </ul>
                    </li>
                @endcan

                {{-- <li class="header-notification">
                    <a href="#!" class="waves-effect waves-light">
                        <i class="ti-bell"></i>
                        <span class="badge bg-c-red"></span>
                    </a>
                    <ul class="show-notification">
                        <li>
                            <h6>Notifications</h6>
                            <label class="label label-danger">New</label>
                        </li>
                        <li class="waves-effect waves-light">
                            <div class="media">
                                <img class="d-flex align-self-center img-radius" src="assets/images/avatar-2.jpg" alt="Generic placeholder image">
                                <div class="media-body">
                                    <h5 class="notification-user">John Doe</h5>
                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                    <span class="notification-time">30 minutes ago</span>
                                </div>
                            </div>
                        </li>
                        <li class="waves-effect waves-light">
                            <div class="media">
                                <img class="d-flex align-self-center img-radius" src="assets/images/avatar-4.jpg" alt="Generic placeholder image">
                                <div class="media-body">
                                    <h5 class="notification-user">Joseph William</h5>
                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                    <span class="notification-time">30 minutes ago</span>
                                </div>
                            </div>
                        </li>
                        <li class="waves-effect waves-light">
                            <div class="media">
                                <img class="d-flex align-self-center img-radius" src="assets/images/avatar-3.jpg" alt="Generic placeholder image">
                                <div class="media-body">
                                    <h5 class="notification-user">Sara Soudein</h5>
                                    <p class="notification-msg">Lorem ipsum dolor sit amet, consectetuer elit.</p>
                                    <span class="notification-time">30 minutes ago</span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li> --}}
                <li class="user-profile header-notification">
                    <a href="#!" class="waves-effect waves-light">
                        <span>{{ Str::ucfirst(Session::get('nome')) }}</span>
                        <i class="ti-angle-down"></i>
                    </a>
                    <ul class="show-notification profile-notification">
                        {{-- <li class="waves-effect waves-light">
                            <a href="{{ route('perfil.edit', Session::get('matricula')) }}">
                                <i class="ti-user"></i> Perfil
                            </a>
                        </li> --}}
                        <li class="waves-effect waves-light">
                            <a href="#" data-toggle="modal" data-target="#alteracao-senha">
                                <i class="fas fa-key"></i> Alterar Senha
                            </a>
                        </li>
                        <li class="waves-effect waves-light">
                            <a href="{{ route('logout') }}">
                                <i class="ti-layout-sidebar-left"></i> Sair
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
@can('acesso', 'bau.lancamentos.anomalias')
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var badge = document.getElementById('estoque-alerta-badge');
                var dropdown = document.getElementById('estoque-alerta-dropdown');
                var empty = document.getElementById('estoque-alerta-empty');
                if (!badge || !dropdown || !empty) return;
                var panelLink = dropdown.querySelector('li[data-role="panel-link"]');

                function atualizarBadge(valor) {
                    var total = Number(valor) || 0;
                    var texto = total > 99 ? '99+' : (total > 0 ? String(total) : '0');
                    badge.textContent = texto;
                    badge.setAttribute('aria-label', texto + ' alerta(s) de estoque');
                }

                atualizarBadge(0);

                fetch('{{ route('bau.lancamentos.anomalias.navbar') }}', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(resp) {
                        if (!resp.ok) throw new Error('Falha ao carregar alerta');
                        return resp.json();
                    })
                    .then(function(data) {
                        var count = Number(data && data.count) || 0;
                        var items = Array.isArray(data && data.items) ? data.items : [];

                        atualizarBadge(count);

                        dropdown.querySelectorAll('li.alert-item').forEach(function(li) {
                            li.remove();
                        });

                        if (!items.length) {
                            empty.textContent = 'Nenhum item critico no momento.';
                            empty.style.display = '';
                            return;
                        }

                        empty.style.display = 'none';

                        var iconConfig = {
                            'Negativo': {
                                icon: 'ti-arrow-down',
                                bg: 'bg-c-red',
                                badge: 'badge-danger'
                            },
                            'Critico': {
                                icon: 'ti-flag',
                                bg: 'bg-c-blue',
                                badge: 'badge-primary'
                            },
                            'Bau limite': {
                                icon: 'ti-layers-alt',
                                bg: 'bg-c-yellow',
                                badge: 'badge-warning'
                            },
                            'Movimento atipico': {
                                icon: 'ti-pulse',
                                bg: 'bg-c-purple',
                                badge: 'badge-info'
                            }
                        };
                        var defaultConfig = {
                            icon: 'ti-info-alt',
                            bg: 'bg-c-blue',
                            badge: 'badge-secondary'
                        };

                        items.forEach(function(item) {
                            var li = document.createElement('li');
                            li.className = 'alert-item waves-effect waves-light';

                            var tipo = item && item.tipo ? item.tipo : 'Alerta';
                            var iconData = iconConfig[tipo] || defaultConfig;
                            var iconClass = iconData.icon + ' ' + iconData.bg;
                            var itemNome = item && item.item ? item.item : '';
                            var origem = item && item.bau ? item.bau : '';
                            var descricaoBruta = item && item.descricao ? String(item.descricao) : '';
                            var descricao = descricaoBruta ? ': ' + descricaoBruta : '';

                            var badgeTexto = '';
                            if (tipo === 'Bau limite') {
                                var percMatch = descricaoBruta.replace(',', '.').match(/[\d.]+%/);
                                if (percMatch && percMatch[0]) {
                                    badgeTexto = percMatch[0].replace('.', ',');
                                }
                            } else if (tipo === 'Critico') {
                                var saldoMatch = descricaoBruta.match(/Saldo:\s*(-?\d+)/i);
                                var limiteMatch = descricaoBruta.match(/Limite:\s*(\d+)/i);
                                if (saldoMatch && limiteMatch) {
                                    badgeTexto = saldoMatch[1] + '/' + limiteMatch[1];
                                }
                            } else if (tipo === 'Negativo') {
                                var negativoMatch = descricaoBruta.match(/-?\d+/);
                                if (negativoMatch) {
                                    badgeTexto = negativoMatch[0];
                                }
                            } else if (tipo === 'Movimento atipico') {
                                var qtdMatch = descricaoBruta.match(/-?\d+/);
                                if (qtdMatch) {
                                    badgeTexto = qtdMatch[0];
                                }
                            }

                            var badgeHtml = badgeTexto ?
                                '<span class="badge notification-metric ' + iconData.badge + '">' +
                                badgeTexto + '</span>' :
                                '';

                            li.innerHTML =
                                '<div class="media">' +
                                '<div class="media-left align-self-center mr-2 alert-icon-wrapper">' +
                                '<i class="' + iconClass +
                                '" style="color:#fff;padding:8px;border-radius:50%;font-size:12px;"></i>' +
                                badgeHtml +
                                '</div>' +
                                '<div class="media-body">' +
                                '<h5 class="notification-user mb-0">' + tipo + '</h5>' +
                                '<small class="text-muted d-block">' + itemNome + '</small>' +
                                '<span class="notification-time">' + origem + descricao + '</span>' +
                                '</div>' +
                                '</div>';

                            if (panelLink) {
                                dropdown.insertBefore(li, panelLink);
                            } else {
                                dropdown.appendChild(li);
                            }
                        });
                    })
                    .catch(function() {
                        empty.textContent = 'Nao foi possivel carregar as informacoes.';
                        empty.style.display = '';
                    });
            });
        </script>
    @endonce
@endcan

@can('acesso', 'bau.lancamentos.solicitacoes.index')
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var badge = document.getElementById('solicitacao-alerta-badge');
                var dropdown = document.getElementById('solicitacao-alerta-dropdown');
                var empty = document.getElementById('solicitacao-alerta-empty');
                if (!badge || !dropdown || !empty) return;

                var linkWrapper = dropdown.querySelector('li[data-role="solicitacao-link"]');

                function atualizarBadge(valor) {
                    var total = Number(valor) || 0;
                    badge.textContent = total > 99 ? '99+' : (total > 0 ? String(total) : '0');
                    badge.setAttribute('aria-label', total + ' solicitação(ões) pendente(s)');
                }

                atualizarBadge(0);

                fetch('{{ route('bau.lancamentos.solicitacoes.navbar') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(function(resp) {
                    if (!resp.ok) throw new Error('Falha ao carregar solicitações');
                    return resp.json();
                }).then(function(data) {
                    var count = Number(data && data.count) || 0;
                    var items = Array.isArray(data && data.items) ? data.items : [];

                    atualizarBadge(count);

                    dropdown.querySelectorAll('li.solicitacao-item').forEach(function(li) {
                        li.remove();
                    });

                    if (!items.length) {
                        empty.innerHTML = '<span class="text-muted">Nenhuma solicitação pendente.</span>';
                        empty.style.display = '';
                        return;
                    }

                    empty.style.display = 'none';

                    items.forEach(function(item) {
                        var li = document.createElement('li');
                        li.className = 'solicitacao-item waves-effect waves-light';
                        var tipo = item && item.tipo ? item.tipo : '—';
                        var usuario = item && item.usuario ? item.usuario : '—';
                        var recebido = item && item.recebido_em ? item.recebido_em : '';
                        var observacao = item && item.observacao ? item.observacao : '';

                        li.innerHTML =
                            '<div class="media">' +
                            '<div class="media-left align-self-center mr-2">' +
                            '<i class="ti-clipboard bg-c-warning" style="color:#fff;padding:8px;border-radius:50%;font-size:12px;"></i>' +
                            '</div>' +
                            '<div class="media-body">' +
                            '<h5 class="notification-user mb-0">' + tipo + (recebido ? ' • ' +
                                recebido : '') + '</h5>' +
                            '<small class="text-muted d-block">Responsável: ' + usuario + '</small>' +
                            '<span class="notification-time">' + (observacao || 'Sem observações') +
                            '</span>' +
                            '</div>' +
                            '</div>';

                        if (linkWrapper) {
                            dropdown.insertBefore(li, linkWrapper);
                        } else {
                            dropdown.appendChild(li);
                        }
                    });
                }).catch(function() {
                    empty.innerHTML =
                        '<span class="text-muted">Não foi possível carregar as solicitações.</span>';
                    empty.style.display = '';
                });
            });
        </script>
    @endonce
@endcan



@can('acesso', 'venda.fila.index')
    @once
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var badge = document.getElementById('vendas-pendentes-badge');
                var dropdown = document.getElementById('vendas-pendentes-dropdown');
                var empty = document.getElementById('vendas-pendentes-empty');
                if (!badge || !dropdown || !empty) return;

                var linkWrapper = dropdown.querySelector('li[data-role="vendas-pendentes-link"]');

                function atualizarBadge(valor) {
                    var total = Number(valor) || 0;
                    var texto = total > 99 ? '99+' : (total > 0 ? String(total) : '0');
                    badge.textContent = texto;
                    badge.setAttribute('aria-label', texto + ' venda(s) pendente(s) de processamento');
                }

                atualizarBadge(0);

                fetch('{{ route('venda.fila.notificacoes-pendentes') }}', {
                    headers: {
                        'Accept': 'application/json'
                    }
                }).then(function(resp) {
                    if (!resp.ok) throw new Error('Falha ao carregar vendas pendentes');
                    return resp.json();
                }).then(function(data) {
                    var count = Number(data && data.total_pendentes) || 0;
                    var items = Array.isArray(data && data.notificacoes) ? data.notificacoes : [];
                    var pendente = Number(data && data.pendente) || 0;
                    var emAtendimento = Number(data && data.em_atendimento) || 0;

                    atualizarBadge(count);

                    // Remover itens anteriores
                    dropdown.querySelectorAll('li.vendas-pendentes-item').forEach(function(li) {
                        li.remove();
                    });

                    if (!items.length) {
                        empty.innerHTML = '<span class="text-muted">Nenhuma venda pendente no momento.</span>';
                        empty.style.display = '';
                        return;
                    }

                    empty.style.display = 'none';



                    // Adicionar cada venda pendente
                    items.forEach(function(item) {
                        var li = document.createElement('li');
                        li.className = 'vendas-pendentes-item waves-effect waves-light';
                        li.style.padding = '8px 15px';
                        li.style.margin = '0';

                        var cliente = item && item.cliente ? item.cliente : '—';
                        var organizacao = item && item.organizacao ? item.organizacao : '—';
                        var statusLabel = item && item.status_label ? item.status_label :
                            'Desconhecido';
                        var dataPedido = item && item.data_pedido ? item.data_pedido : '—';
                        var diasPendente = Number(item && item.dias_pendente) || 0;
                        var urgencia = item && item.urgencia ? item.urgencia : {
                            icon: 'ti-shopping-cart',
                            color: 'bg-c-blue',
                            class: 'text-primary'
                        };

                        var badgeTexto = '';
                        if (diasPendente > 0) {
                            badgeTexto = '<span class="badge badge-secondary notification-metric">' +
                                diasPendente + 'd</span>';
                        }

                        li.innerHTML =
                            '<div class="media">' +
                            '<div class="media-left align-self-center mr-2">' +
                            '<i class="' + urgencia.icon + ' ' + urgencia.color +
                            '" style="color:#fff;padding:8px;border-radius:50%;font-size:12px;"></i>' +
                            badgeTexto +
                            '</div>' +
                            '<div class="media-body" style="line-height: 1.2;">' +
                            '<h5 class="notification-user mb-0 ' + urgencia.class +
                            '" style="font-size: 14px; margin-bottom: 2px !important;">' + cliente +
                            '</h5>' +
                            '<span class="notification-time" style="font-size: 11px; color: #999;">Pedido em: ' +
                            dataPedido + '</span>' +
                            '</div>' +
                            '</div>';

                        if (linkWrapper) {
                            dropdown.insertBefore(li, linkWrapper);
                        } else {
                            dropdown.appendChild(li);
                        }
                    });

                }).catch(function(error) {
                    console.error('Erro ao carregar vendas pendentes:', error);
                    empty.innerHTML =
                        '<span class="text-muted">Não foi possível carregar as informações.</span>';
                    empty.style.display = '';
                });

                // Atualizar a cada 3 minutos (vendas são mais dinâmicas)
                setInterval(function() {
                    window.location.reload();
                }, 180000);
            });
        </script>
    @endonce
@endcan
