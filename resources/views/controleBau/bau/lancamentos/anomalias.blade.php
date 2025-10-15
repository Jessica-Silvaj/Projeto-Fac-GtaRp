@extends('layouts.master', ['titulo' => 'Dashboard de Anomalias', 'subtitulo' => 'Monitoramento de riscos no estoque'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('bau.lancamentos.anomalias') }}" method="GET">
                <div class="card-block">
                    <div class="form-row align-items-end">
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" name="inicio" class="form-control data-mask" data-format="DD/MM/YYYY"
                                value="{{ !empty($inicio) ? \Carbon\Carbon::parse($inicio)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Desde</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" name="fim" class="form-control data-mask" data-format="DD/MM/YYYY"
                                value="{{ !empty($fim) ? \Carbon\Carbon::parse($fim)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Até</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <select name="itens_id" class="form-control select2"
                                data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}"
                                placeholder="Filtrar por item">
                                <option value=""></option>
                                @if (!empty($itemSelecionado))
                                    <option value="{{ $itemSelecionado['id'] }}" selected>{{ $itemSelecionado['nome'] }}
                                    </option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Item</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <select name="bau_id" class="form-control select2"
                                data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}"
                                placeholder="Filtrar por baú">
                                <option value=""></option>
                                @if (!empty($bauSelecionado))
                                    <option value="{{ $bauSelecionado['id'] }}" selected>{{ $bauSelecionado['nome'] }}
                                    </option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Baú</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                        <i class="ti-search"></i> Atualizar
                    </button>
                    <a href="{{ route('bau.lancamentos.anomalias') }}"
                        class="btn btn-sm btn-secondary btn-out-dashed waves-effect waves-light ml-2">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-1">{{ number_format($totais['negativos'] ?? 0, 0, ',', '.') }}</h3>
                        <p class="mb-0 text-muted">Itens com estoque negativo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1">{{ number_format($totais['estoquesCriticos'] ?? 0, 0, ',', '.') }}
                        </h3>
                        <p class="mb-0 text-muted">Itens em nível crítico</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1">{{ number_format($totais['bausCriticos'] ?? 0, 0, ',', '.') }}</h3>
                        <p class="mb-0 text-muted">Baús acima do limite</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-1">{{ number_format($totais['movimentosAtipicos'] ?? 0, 0, ',', '.') }}
                        </h3>
                        <p class="mb-0 text-muted">Movimentações atí­picas (últimos dias)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php $tab = request()->get('tab', 'negativos'); @endphp
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="anomalia-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'negativos' ? 'active' : '' }}" href="#!"
                            data-tab-target="negativos">Estoques negativos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'criticos' ? 'active' : '' }}" href="#!"
                            data-tab-target="criticos">Estoques crí­ticos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'baus' ? 'active' : '' }}" href="#!"
                            data-tab-target="baus">Baús acima do limite</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'movimentos' ? 'active' : '' }}" href="#!"
                            data-tab-target="movimentos">Movimentações atí­picas</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="anomalia-tabs-content">
                    <div class="tab-pane {{ $tab === 'negativos' ? 'active show' : '' }}" data-tab="negativos">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Baú</th>
                                        <th class="text-right">Saldo</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($negativos as $row)
                                        <tr>
                                            <td>{{ $row['item_nome'] }}</td>
                                            <td>{{ $row['bau_nome'] }}</td>
                                            <td class="text-right text-danger">
                                                {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                                            <td class="text-right">
                                                @can('acesso', 'bau.lancamentos.historico')
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('bau.lancamentos.historico') .
                                                            '?' .
                                                            http_build_query([
                                                                'itens_id' => $row['itens_id'] ?? null,
                                                                'bau_id' => $row['bau_id'] ?? null,
                                                                'bau_destino_id' => $row['bau_id'] ?? null,
                                                                'bau_origem_id' => $row['bau_id'] ?? null,
                                                            ]) }}">
                                                        Ver histórico
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Nenhum item negativo
                                                encontrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($negativos->hasPages())
                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2">
                                    Mostrando
                                    <strong>{{ $negativos->firstItem() ?? 0 }}</strong> -
                                    <strong>{{ $negativos->lastItem() ?? 0 }}</strong>
                                    de <strong>{{ $negativos->total() }}</strong> registros.
                                </div>
                                <div>
                                    {{ $negativos->appends(array_merge(request()->except(['negativos_page', 'criticos_page', 'baus_page', 'movimentos_page']), ['tab' => 'negativos']))->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane {{ $tab === 'criticos' ? 'active show' : '' }}" data-tab="criticos">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Baú</th>
                                        <th class="text-right">Quantidade</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($estoquesCriticos as $row)
                                        <tr>
                                            <td>{{ $row['item_nome'] }}</td>
                                            <td>{{ $row['bau_nome'] }}</td>
                                            <td class="text-right text-primary">
                                                {{ number_format($row['quantidade'], 0, ',', '.') }}</td>
                                            <td class="text-right">
                                                @can('acesso', 'bau.lancamentos.estoque')
                                                    <a class="btn btn-sm btn-primary"
                                                        href="{{ route('bau.lancamentos.estoque') .
                                                            '?' .
                                                            http_build_query([
                                                                'itens_id' => $row['itens_id'] ?? null,
                                                                'bau_id' => $row['bau_id'] ?? null,
                                                            ]) }}">
                                                        Ver estoque
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Nenhum item com estoque
                                                crí­tico.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($estoquesCriticos->hasPages())
                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2">
                                    Mostrando
                                    <strong>{{ $estoquesCriticos->firstItem() ?? 0 }}</strong> -
                                    <strong>{{ $estoquesCriticos->lastItem() ?? 0 }}</strong>
                                    de <strong>{{ $estoquesCriticos->total() }}</strong> registros.
                                </div>
                                <div>
                                    {{ $estoquesCriticos->appends(array_merge(request()->except(['negativos_page', 'criticos_page', 'baus_page', 'movimentos_page']), ['tab' => 'criticos']))->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane {{ $tab === 'baus' ? 'active show' : '' }}" data-tab="baus">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Baú</th>
                                        <th class="text-right">Quantidade</th>
                                        <th class="text-right">Limite</th>
                                        <th class="text-right">% Ocupado</th>
                                        <th class="text-right">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bausCriticos as $row)
                                        <tr>
                                            <td>{{ $row['bau_nome'] }}</td>
                                            <td class="text-right">{{ number_format($row['quantidade'], 0, ',', '.') }}
                                            </td>
                                            <td class="text-right">{{ number_format($row['limite'], 0, ',', '.') }}</td>
                                            <td class="text-right">
                                                {{ number_format(($row['ocupacao_percentual'] ?? 0) * 100, 1, ',', '.') }}%
                                            </td>
                                            <td class="text-right">
                                                @can('acesso', 'bau.lancamentos.estoque')
                                                    <a class="btn btn-sm btn-outline-secondary"
                                                        href="{{ route('bau.lancamentos.estoque') . '?' . http_build_query(['bau_id' => $row['bau_id'] ?? null]) }}">
                                                        Ver estoque
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Nenhum baú acima do limite
                                                configurado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($bausCriticos->hasPages())
                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2">
                                    Mostrando
                                    <strong>{{ $bausCriticos->firstItem() ?? 0 }}</strong> -
                                    <strong>{{ $bausCriticos->lastItem() ?? 0 }}</strong>
                                    de <strong>{{ $bausCriticos->total() }}</strong> registros.
                                </div>
                                <div>
                                    {{ $bausCriticos->appends(array_merge(request()->except(['negativos_page', 'criticos_page', 'baus_page', 'movimentos_page']), ['tab' => 'baus']))->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane {{ $tab === 'movimentos' ? 'active show' : '' }}" data-tab="movimentos">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Item</th>
                                        <th class="text-right">Quantidade</th>
                                        <th>Tipo</th>
                                        <th>Baú origem</th>
                                        <th>Baú destino</th>
                                        <th>Usuário</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($movimentosAtipicos as $row)
                                        <tr>
                                            <td>{{ $row['data'] }}</td>
                                            <td>{{ $row['item'] }}</td>
                                            <td class="text-right">{{ number_format($row['quantidade'], 0, ',', '.') }}
                                            </td>
                                            <td>{{ $row['tipo'] }}</td>
                                            <td>{{ $row['bau_origem'] }}</td>
                                            <td>{{ $row['bau_destino'] }}</td>
                                            <td>{{ $row['usuario'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Nenhuma movimentação fora do
                                                padrão encontrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if ($movimentosAtipicos->hasPages())
                            <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2">
                                    Mostrando
                                    <strong>{{ $movimentosAtipicos->firstItem() ?? 0 }}</strong> -
                                    <strong>{{ $movimentosAtipicos->lastItem() ?? 0 }}</strong>
                                    de <strong>{{ $movimentosAtipicos->total() }}</strong> registros.
                                </div>
                                <div>
                                    {{ $movimentosAtipicos->appends(array_merge(request()->except(['negativos_page', 'criticos_page', 'baus_page', 'movimentos_page']), ['tab' => 'movimentos']))->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            var tabs = document.querySelectorAll('#anomalia-tabs a[data-tab-target]');
            var panes = document.querySelectorAll('#anomalia-tabs-content .tab-pane');
            if (!tabs.length) return;

            function activate(tabName, pushState) {
                tabs.forEach(function(tab) {
                    if (tab.getAttribute('data-tab-target') === tabName) {
                        tab.classList.add('active');
                    } else {
                        tab.classList.remove('active');
                    }
                });
                panes.forEach(function(pane) {
                    if (pane.getAttribute('data-tab') === tabName) {
                        pane.classList.add('active', 'show');
                    } else {
                        pane.classList.remove('active', 'show');
                    }
                });
                if (pushState && history.pushState) {
                    var url = new URL(window.location.href);
                    url.searchParams.set('tab', tabName);
                    history.replaceState(null, '', url.toString());
                }
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    var target = tab.getAttribute('data-tab-target');
                    activate(target, true);
                });
            });

            var current = '{{ $tab }}';
            activate(current, false);
        })();
    </script>
@endsection
