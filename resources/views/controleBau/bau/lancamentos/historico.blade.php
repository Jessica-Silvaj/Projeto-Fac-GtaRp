@extends('layouts.master', ['titulo' => 'Histórico de Lançamentos', 'subtitulo' => 'Entradas e Saídas (Últimos 30 dias)'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Periodo</h3>
            </div>
            <form id="hist-form" class="form-material" action="{{ route('bau.lancamentos.historico') }}" method="GET"
                role="">
                <div class="card-block">
                    <div class="form-row align-items-end">
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" id="inicio" name="inicio" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ !empty($inicio) ? \Carbon\Carbon::parse($inicio)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label for="inicio" class="float-label">Inicio</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" id="fim" name="fim" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ !empty($fim) ? \Carbon\Carbon::parse($fim)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label for="fim" class="float-label">Fim</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4 mb-3">
                            <select name="itens_id" id="itens_id" class="form-control select2"
                                data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}"
                                placeholder="Filtrar por item">
                                <option value=""></option>
                                @if (!empty($itemSelecionado))
                                    <option value="{{ $itemSelecionado['id'] }}" selected>{{ $itemSelecionado['nome'] }}
                                    </option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label for="itens_id" class="float-label">Item (opcional)</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-2 mb-3">
                            <select name="granularidade" id="granularidade" class="form-control select2"
                                placeholder="Exibição">
                                <option value="dia" @selected(($granularidade ?? 'dia') === 'dia')>Dia</option>
                                <option value="semana" @selected(($granularidade ?? 'dia') === 'semana')>Semana</option>
                                <option value="mes" @selected(($granularidade ?? 'dia') === 'mes')>Mês</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="granularidade" class="float-label">Granularidade</label>
                        </div>
                    </div>
                    <div class="form-row align-items-end mt-2">
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <select name="tipo" id="tipo" class="form-control select2"
                                placeholder="Tipo (opcional)">
                                @php $tipoSel = (string) request()->get('tipo', ''); @endphp
                                <option value=""></option>
                                <option value="ENTRADA" @selected($tipoSel === 'ENTRADA')>ENTRADA</option>
                                <option value="SAIDA" @selected($tipoSel === 'SAIDA')>SAIDA</option>
                                <option value="TRANSFERENCIA" @selected($tipoSel === 'TRANSFERENCIA')>TRANSFERÊNCIA</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="tipo" class="float-label">Tipo</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <select name="bau_origem_id" id="bau_origem_id" class="form-control select2"
                                data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}"
                                placeholder="Baú Origem (opcional)">
                                <option value=""></option>
                                @if (!empty($bauOrigemSelecionado))
                                    <option value="{{ $bauOrigemSelecionado['id'] }}" selected>
                                        {{ $bauOrigemSelecionado['nome'] }}</option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label for="bau_origem_id" class="float-label">Baú Origem (opcional)</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4 mb-3">
                            <select name="bau_destino_id" id="bau_destino_id" class="form-control select2"
                                data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}"
                                placeholder="Baú Destino (opcional)">
                                <option value=""></option>
                                @if (!empty($bauDestinoSelecionado))
                                    <option value="{{ $bauDestinoSelecionado['id'] }}" selected>
                                        {{ $bauDestinoSelecionado['nome'] }}</option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label for="bau_destino_id" class="float-label">Baú Destino (opcional)</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-2 mb-3">
                            <select name="usuario_id" id="usuario_id" class="form-control select2"
                                placeholder="Usuário (opcional)">
                                <option value=""></option>
                                @foreach ($usuariosBau ?? [] as $usuario)
                                    <option value="{{ $usuario['id'] }}" @selected(($usuarioFiltroId ?? 0) === $usuario['id'])>
                                        {{ $usuario['nome'] }}</option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="usuario_id" class="float-label">Usuário (opcional)</label>
                        </div>
                    </div>
                    <div class="form-row align-items-center mt-2">
                        <div class="form-group col-md-12 mb-0 metric-options text-center">
                            <label class="d-inline-block mr-3 mb-1 metric-title">Métrica</label>
                            <label class="form-check form-check-inline mb-0 mr-3">
                                <input class="form-check-input" type="radio" name="modo" id="modo_quantidade"
                                    value="quantidade" {{ ($modo ?? 'quantidade') === 'quantidade' ? 'checked' : '' }}>
                                <span class="form-check-label">Quantidade</span>
                            </label>
                            <label class="form-check form-check-inline mb-0">
                                <input class="form-check-input" type="radio" name="modo" id="modo_movimentos"
                                    value="movimentos" {{ ($modo ?? '') === 'movimentos' ? 'checked' : '' }}>
                                <span class="form-check-label">Movimentações</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Atualizar
                            </button>
                            <button type="button" id="btn-limpar"
                                class="btn btn-sm btn-secondary btn-out-dashed waves-effect waves-light ml-2">
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="mb-0">Exportações</h3>
                <div class="text-right export-actions">
                    @php
                        $estoqueQuery = array_filter([
                            'itens_id' => request()->get('itens_id'),
                            'bau_id' => request()->get('bau_destino_id') ?: request()->get('bau_origem_id'),
                        ]);
                        $estoqueUrl =
                            route('bau.lancamentos.estoque') .
                            ($estoqueQuery ? '?' . http_build_query($estoqueQuery) : '');
                    @endphp
                    @can('acesso', 'bau.lancamentos.estoque')
                        <a class="btn btn-sm btn-primary mr-2" href="{{ $estoqueUrl }}">
                            Ver estoque total
                        </a>
                    @endcan
                    @can('acesso', 'bau.lancamentos.historico.csv')
                        <a class="btn btn-sm btn-outline-secondary btn-export"
                            href="{{ route('bau.lancamentos.historico.csv', array_merge(request()->all(), ['dataset' => 'serie'])) }}">Serie
                            CSV</a>
                        <a class="btn btn-sm btn-outline-secondary btn-export"
                            href="{{ route('bau.lancamentos.historico.csv', array_merge(request()->all(), ['dataset' => 'top_entradas'])) }}">Top
                            Entradas CSV</a>
                        <a class="btn btn-sm btn-outline-secondary btn-export"
                            href="{{ route('bau.lancamentos.historico.csv', array_merge(request()->all(), ['dataset' => 'top_saidas'])) }}">Top
                            Saídas CSV</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Total de Entradas</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-0 text-success">{{ number_format($totais['entradas'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Total de Saídas</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-0 text-danger">{{ number_format($totais['saidas'] ?? 0, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Saldo Líquido</h3>
                    </div>
                    <div class="card-body">
                        @php $liquido = ($totais['entradas'] ?? 0) - ($totais['saidas'] ?? 0); @endphp
                        <h2 class="mb-0 {{ $liquido >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($liquido, 0, ',', '.') }}</h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">Entradas x Saídas por
                            {{ ($granularidade ?? 'dia') === 'semana' ? 'semana' : (($granularidade ?? 'dia') === 'mes' ? 'mês' : 'dia') }}
                            ({{ ($modo ?? 'quantidade') === 'movimentos' ? 'Movimentações' : 'Quantidade' }})</h3>
                    </div>
                    <div class="card-body">
                        <div id="chart-geral" style="height: 300px; overflow: hidden; position: relative;"></div>
                        <hr class="my-3">
                        <h6 class="mb-2">Saldo acumulado</h6>
                        <div id="chart-saldo" style="height: 220px; overflow: hidden; position: relative;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4" id="col-right-historico">
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">Top Itens (Entradas
                            {{ ($modo ?? 'quantidade') === 'movimentos' ? 'Movimentações' : 'Quantidade' }})</h3>
                    </div>
                    <div class="card-body">
                        <div id="donut-entradas" style="height: 200px;"></div>
                        <div id="legend-entradas" class="chart-legend mt-2"></div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">Top Itens (Saídas
                            {{ ($modo ?? 'quantidade') === 'movimentos' ? 'Movimentações' : 'Quantidade' }})</h3>
                    </div>
                    <div class="card-body">
                        <div id="donut-saidas" style="height: 200px;"></div>
                        <div id="legend-saidas" class="chart-legend mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="mb-0">Detalhes do período</h3>
                <button type="button" id="btn-load-detalhes" class="btn btn-sm btn-outline-secondary">Carregar
                    detalhes</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Tipo</th>
                                <th>Item</th>
                                <th class="text-right">Qtd</th>
                                <th>Baú Origem</th>
                                <th>Baú Destino</th>
                                <th>Usuário</th>
                                <th>Obs</th>
                            </tr>
                        </thead>
                        <tbody id="detalhes-tbody">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Clique em "Carregar detalhes"</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/js/morris.js/morris.js') }}"></script>
    <style>
        .chart-legend {
            max-height: 140px;
            overflow-y: auto;
        }

        /* deixa as métricas com a mesma cor dos labels do tema (float-label) */
        .metric-options .metric-title,
        .metric-options .form-check-label {
            color: #d59725;
        }

        /* exportações com a mesma cor do tema */
        .export-actions .btn-export {
            border-color: #d59725;
            color: #d59725;
        }

        .export-actions .btn-export:hover {
            background-color: #d59725;
            color: #fff;
        }
    </style>
    <script>
        (function() {
            // Converte datas dd/mm/yyyy para yyyy-mm-dd no submit
            var formEl = document.getElementById('hist-form');

            function toISO(d) {
                if (!d) return "";
                var m = d.match(/^(\d{1,2})\/(\d{1,2})\/(\d{2,4})$/);
                if (!m) return d;
                var dd = ("0" + m[1]).slice(-2),
                    mm = ("0" + m[2]).slice(-2),
                    yy = m[3].length === 2 ? ("20" + m[3]) : m[3];
                return yy + "-" + mm + "-" + dd;
            }
            if (formEl) {
                formEl.addEventListener('submit', function() {
                    var iv = document.getElementById('inicio'),
                        fv = document.getElementById('fim');
                    if (iv) iv.value = toISO(iv.value);
                    if (fv) fv.value = toISO(fv.value);
                });
            }
            var serie = @json($serie);
            var saldoSerie = @json($saldoSerie);
            var granularidade = @json($granularidade ?? 'dia');
            var donutEntradas = @json($entradasPorItem);
            var donutSaidas = @json($saidasPorItem);
            var donutEntradasAll = @json($entradasPorItemTodos);
            var donutSaidasAll = @json($saidasPorItemTodos);

            function renderLegend(containerId, data, colors) {
                var el = document.getElementById(containerId);
                if (!el) return;
                el.innerHTML = '';
                var total = 0;
                (data || []).forEach(function(d) {
                    total += (parseInt(d.value, 10) || 0);
                });
                data.forEach(function(d, i) {
                    var color = colors[i % colors.length];
                    var item = document.createElement('div');
                    item.style.display = 'flex';
                    item.style.alignItems = 'center';
                    item.style.gap = '8px';
                    item.style.marginBottom = '4px';
                    item.style.cursor = 'pointer';
                    var sw = document.createElement('span');
                    sw.style.display = 'inline-block';
                    sw.style.width = '12px';
                    sw.style.height = '12px';
                    sw.style.borderRadius = '2px';
                    sw.style.background = color;
                    var tx = document.createElement('span');
                    var pct = total > 0 ? ((d.value / total) * 100).toFixed(1) + '%' : '';
                    tx.textContent = d.label + ' (' + d.value + (pct ? ' — ' + pct : '') + ')';
                    item.appendChild(sw);
                    item.appendChild(tx);
                    item.addEventListener('click', function() {
                        if (!d.id) return;
                        var form = document.getElementById('hist-form');
                        var sel = document.getElementById('itens_id');
                        if (form && sel) {
                            sel.innerHTML = '';
                            var opt = document.createElement('option');
                            opt.value = d.id;
                            opt.textContent = d.label;
                            opt.selected = true;
                            sel.appendChild(opt);
                            form.submit();
                        }
                    });
                    el.appendChild(item);
                });
            }

            var mainChart = null;
            var saldoChart = null;

            function drawBarAligned() {
                var cont = document.getElementById('chart-geral');
                if (!cont) return;
                var lastCard = document.querySelector('#col-right-historico .card:last-child');
                if (lastCard) {
                    var bottom = lastCard.getBoundingClientRect().bottom + window.scrollY;
                    var top = cont.getBoundingClientRect().top + window.scrollY;
                    var h = bottom - top; // altura exata até o fim do card à direita
                    // Margem de respiro e limites
                    h = Math.max(220, Math.min(1200, h));
                    cont.style.height = h + 'px';
                }
                if (mainChart) {
                    cont.innerHTML = '';
                    mainChart = null;
                }
                var ratio = serie && serie.length ? Math.max(0.25, Math.min(0.8, 16 / serie.length)) : 0.6;
                mainChart = new Morris.Bar({
                    element: 'chart-geral',
                    data: serie,
                    xkey: 'y',
                    ykeys: ['entradas', 'saidas'],
                    labels: ['entradas', 'saidas'],
                    hideHover: 'auto',
                    xLabelAngle: 45,
                    resize: true,
                    barColors: ['#28a745', '#dc3545'],
                    barSizeRatio: ratio
                });
                // suavização visual: cantos arredondados das barras
                setTimeout(function() {
                    var rects = document.querySelectorAll('#chart-geral svg rect');
                    rects.forEach(function(r) {
                        r.setAttribute('rx', 3);
                        r.setAttribute('ry', 3);
                    });
                }, 0);
                // saldo chart (line)
                var contSaldo = document.getElementById('chart-saldo');
                if (contSaldo) {
                    contSaldo.innerHTML = '';
                    saldoChart = new Morris.Line({
                        element: 'chart-saldo',
                        data: saldoSerie || [],
                        xkey: 'y',
                        ykeys: ['saldo'],
                        labels: ['Saldo'],
                        lineColors: ['#6f42c1'],
                        parseTime: false,
                        hideHover: 'auto',
                        resize: true
                    });
                }
            }

            var donutE = null,
                donutS = null;
            var showingAllE = false,
                showingAllS = false;
            var colorsE = ['#2ca02c', '#98df8a', '#1f77b4', '#aec7e8', '#ff7f0e', '#ffbb78', '#d62728', '#ff9896',
                '#9467bd', '#c5b0d5'
            ];
            var colorsS = ['#d62728', '#ff9896', '#1f77b4', '#aec7e8', '#2ca02c', '#98df8a', '#ff7f0e', '#ffbb78',
                '#9467bd', '#c5b0d5'
            ];

            function desiredDonutHeight(id) {
                var el = document.getElementById(id);
                if (!el) return 200;
                var w = el.offsetWidth || (el.parentElement ? el.parentElement.offsetWidth : 300);
                // Deixe os donuts mais "finos" (menos altos) conforme granularidade
                var ratio = 0.45; // padrão mais fino
                if (granularidade === 'mes') ratio = 0.38;
                else if (granularidade === 'semana') ratio = 0.42;
                else if (granularidade === 'dia') ratio = 0.48;
                var h = Math.max(120, Math.min(240, Math.round(w * ratio)));
                return h;
            }

            function buildDonutE() {
                var el = document.getElementById('donut-entradas');
                if (!el) return;
                el.style.height = desiredDonutHeight('donut-entradas') + 'px';
                el.innerHTML = '';
                donutE = new Morris.Donut({
                    element: 'donut-entradas',
                    data: showingAllE ? donutEntradasAll : donutEntradas,
                    colors: colorsE
                });
                renderLegend('legend-entradas', showingAllE ? donutEntradasAll : donutEntradas, colorsE);
            }

            function buildDonutS() {
                var el = document.getElementById('donut-saidas');
                if (!el) return;
                el.style.height = desiredDonutHeight('donut-saidas') + 'px';
                el.innerHTML = '';
                donutS = new Morris.Donut({
                    element: 'donut-saidas',
                    data: showingAllS ? donutSaidasAll : donutSaidas,
                    colors: colorsS
                });
                renderLegend('legend-saidas', showingAllS ? donutSaidasAll : donutSaidas, colorsS);
            }

            // inicializa donuts e botões ver mais/menos
            if (document.getElementById('donut-entradas')) {
                buildDonutE();
                var btnE = document.createElement('a');
                btnE.href = 'javascript:void(0)';
                btnE.textContent = 'ver mais';
                btnE.className = 'mt-2 d-inline-block';
                btnE.onclick = function() {
                    showingAllE = !showingAllE;
                    buildDonutE();
                    btnE.textContent = showingAllE ? 'ver menos' : 'ver mais';
                    setTimeout(drawBarAligned, 50);
                };
                document.getElementById('legend-entradas').appendChild(btnE);
            }

            if (document.getElementById('donut-saidas')) {
                buildDonutS();
                var btnS = document.createElement('a');
                btnS.href = 'javascript:void(0)';
                btnS.textContent = 'ver mais';
                btnS.className = 'mt-2 d-inline-block';
                btnS.onclick = function() {
                    showingAllS = !showingAllS;
                    buildDonutS();
                    btnS.textContent = showingAllS ? 'ver menos' : 'ver mais';
                    setTimeout(drawBarAligned, 50);
                };
                document.getElementById('legend-saidas').appendChild(btnS);
            }

            // render inicial e ao redimensionar janela
            setTimeout(function() {
                buildDonutE();
                buildDonutS();
                drawBarAligned();
            }, 50);
            var resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    buildDonutE();
                    buildDonutS();
                    drawBarAligned();
                }, 120);
            });
            // Copiar link
            var copyBtn = document.getElementById('btn-copy-link');
            if (copyBtn) {
                copyBtn.addEventListener('click', function() {
                    var url = window.location.origin + window.location.pathname + '?' + new URLSearchParams(
                        new FormData(document.getElementById('hist-form'))).toString();
                    navigator.clipboard.writeText(url).then(function() {
                        copyBtn.textContent = 'Link copiado!';
                        setTimeout(function() {
                            copyBtn.textContent = 'Copiar link';
                        }, 1500);
                    });
                });
            }
            // Limpar filtros e recarregar
            var clearBtn = document.getElementById('btn-limpar');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    var form = document.getElementById('hist-form');
                    if (!form) return;
                    // limpar campos principais
                    var inicio = document.getElementById('inicio');
                    var fim = document.getElementById('fim');
                    var itens = document.getElementById('itens_id');
                    var gran = document.getElementById('granularidade');
                    var modoQ = document.getElementById('modo_quantidade');
                    if (inicio) inicio.value = '';
                    if (fim) fim.value = '';
                    if (itens) {
                        itens.value = '';
                        try {
                            itens.innerHTML = '<option value=""></option>';
                        } catch (e) {}
                    }
                    var tipo = document.getElementById('tipo');
                    if (tipo) {
                        tipo.value = '';
                    }
                    if (gran) gran.value = 'dia';
                    if (modoQ) modoQ.checked = true;
                    // campos opcionais se existirem
                    var o = document.getElementById('bau_origem_id');
                    if (o) o.value = '';
                    var d = document.getElementById('bau_destino_id');
                    if (d) d.value = '';
                    var u = document.getElementById('usuario_id');
                    if (u) u.value = '';
                    form.submit();
                });
            }
            // Carregar detalhes
            var detBtn = document.getElementById('btn-load-detalhes');
            if (detBtn) {
                detBtn.addEventListener('click', function() {
                    var params = new URLSearchParams(new FormData(document.getElementById('hist-form')));
                    fetch('{{ route('bau.lancamentos.historico.detalhes') }}' + '?' + params.toString())
                        .then(r => r.json())
                        .then(function(resp) {
                            var tb = document.getElementById('detalhes-tbody');
                            if (!tb) return;
                            tb.innerHTML = '';
                            var dados = (resp && resp.detalhes) ? resp.detalhes : [];
                            if (!dados.length) {
                                tb.innerHTML =
                                    '<tr><td colspan="8" class="text-center text-muted">Sem registros</td></tr>';
                                return;
                            }
                            dados.forEach(function(d) {
                                var tr = document.createElement('tr');
                                var badge = d.fabricacao_auto ?
                                    ' <span class="badge badge-warning">FABRICACÃO</span>' : '';
                                tr.innerHTML = '<td>' + (d.data || '') + '</td>' +
                                    '<td>' + (d.tipo || '') + '</td>' +
                                    '<td>' + (d.item || '') + badge + '</td>' +
                                    '<td class="text-right">' + (d.quantidade || 0) + '</td>' +
                                    '<td>' + (d.bau_origem || '') + '</td>' +
                                    '<td>' + (d.bau_destino || '') + '</td>' +
                                    '<td>' + (d.usuario || '') + '</td>' +
                                    '<td>' + (d.observacao || '') + '</td>';
                                tb.appendChild(tr);
                            });
                        })
                        .catch(function() {
                            var tb = document.getElementById('detalhes-tbody');
                            if (tb) tb.innerHTML =
                                '<tr><td colspan="8" class="text-center text-danger">Erro ao carregar</td></tr>';
                        });
                });
            }
        })();
    </script>
@endsection
