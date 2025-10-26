@extends('layouts.master', [
    'titulo' => 'Historico de Vendas',
    'subtitulo' => 'Resumo das vendas concluidas',
])

@php
    // Função para formatar moeda sem casas decimais desnecessárias
    $formatCurrency = function ($value) {
        $num = (float) $value;
        if ($num == (int) $num) {
            // Se é número inteiro, não mostrar casas decimais
            return 'R$ ' . number_format($num, 0, ',', '.');
        } else {
            // Se tem decimais, mostrar 2 casas
            return 'R$ ' . number_format($num, 2, ',', '.');
        }
    };

    $formatNumber = fn($value) => number_format((float) $value, 0, '', '.');

    $totalLimpo = (float) ($totais['total_limpo'] ?? 0);
    $totalSujo = (float) ($totais['total_sujo'] ?? 0);

    $mixTipo = collect([['label' => 'Limpo', 'value' => $totalLimpo], ['label' => 'Sujo', 'value' => $totalSujo]])
        ->filter(fn($item) => $item['value'] > 0)
        ->values();

    $donutOrganizacoes = collect($rankings['organizacoes'] ?? [])
        ->take(8)
        ->map(
            fn($item) => [
                'label' => is_object($item) ? $item->nome : $item['nome'] ?? 'Não informado',
                'value' => (float) (is_object($item) ? $item->faturamento : $item['faturamento'] ?? 0),
            ],
        )
        ->values();

    $donutSolicitantes = collect($rankings['solicitantes'] ?? [])
        ->take(8)
        ->map(
            fn($item) => [
                'label' => is_object($item) ? $item->nome : $item['nome'] ?? 'Não informado',
                'value' => (float) (is_object($item) ? $item->faturamento : $item['faturamento'] ?? 0),
            ],
        )
        ->values();

    $donutResponsaveis = collect($rankings['responsaveis'] ?? [])
        ->take(8)
        ->map(
            fn($item) => [
                'label' => is_object($item) ? $item->responsavel : $item['responsavel'] ?? 'Não informado',
                'value' => (float) (is_object($item) ? $item->faturamento : $item['faturamento'] ?? 0),
            ],
        )
        ->values();
@endphp

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">Histórico de Vendas</h5>
                        <small class="text-muted">Análise de vendas concluídas por período</small>
                    </div>
                    <div class="col-md-4 text-right">
                        @can('acesso', 'venda.fila.index')
                            <a href="{{ route('venda.fila.index') }}"
                                class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                                <i class="ti-arrow-left"></i> Voltar para fila
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <form class="form-material" method="GET" action="{{ route('venda.fila.historico') }}">
                <div class="card-block">
                    <div class="row">
                        <div class="form-group form-default form-static-label col-md-5">
                            <input type="date" name="data_inicio" id="data_inicio" class="form-control"
                                value="{{ old('data_inicio', $filtros['data_inicio'] ?? optional($periodo['inicio'])->format('Y-m-d')) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data inicial</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-5">
                            <input type="date" name="data_fim" id="data_fim" class="form-control"
                                value="{{ old('data_fim', $filtros['data_fim'] ?? optional($periodo['fim'])->format('Y-m-d')) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data final</label>
                        </div>
                        <div class="form-group col-md-2 d-flex align-items-end">
                            @can('acesso', 'venda.fila.historico')
                                <button type="submit"
                                    class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light mr-1">
                                    <i class="ti-search"></i> Filtrar
                                </button>
                                <a href="{{ route('venda.fila.historico') }}"
                                    class="btn btn-secondary btn-sm btn-out-dashed waves-effect waves-light">
                                    <i class="ti-reload"></i> Limpar
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success text-white rounded">
                                <i class="ti-package" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $formatNumber($totais['total_vendas'] ?? 0) }}</h4>
                                <span class="text-muted">Vendas Concluídas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary text-white rounded">
                                <i class="ti-wallet" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $formatCurrency($totais['faturamento_total'] ?? 0) }}</h4>
                                <span class="text-muted">Faturamento Total</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning text-white rounded">
                                <i class="ti-stats-up" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $formatCurrency($totais['ticket_medio'] ?? 0) }}</h4>
                                <span class="text-muted">Ticket Médio</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12 mt-3">
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Fluxo diario (limpo x sujo)</h3>
                        <small class="text-muted">Atualizado dinamicamente</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-daily" class="chart-block"></div>
                        <div class="chart-pagination text-center mt-3" id="daily-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mt-lg-0">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Composicao por tipo</h3>
                        <small class="text-muted">Somente periodos com valores</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-tipo" class="chart-donut"></div>
                        <div id="legend-tipo" class="chart-legend mt-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Faturamento semanal</h3>
                        <small class="text-muted">Ultimas semanas</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-weekly" class="chart-block"></div>
                        <div class="chart-pagination text-center mt-3" id="weekly-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Faturamento mensal</h3>
                        <small class="text-muted">Ultimos meses</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-monthly" class="chart-block"></div>
                        <div class="chart-pagination text-center mt-3" id="monthly-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Top organizacoes</h3>
                        <small class="text-muted">Por faturamento</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-organizacoes" class="chart-donut"></div>
                        <div id="legend-organizacoes" class="chart-legend mt-2"></div>
                        <div class="chart-pagination text-center mt-2" id="organizacoes-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Compradores recorrentes</h3>
                        <small class="text-muted">Solicitantes mais frequentes</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-solicitantes" class="chart-donut"></div>
                        <div id="legend-solicitantes" class="chart-legend mt-2"></div>
                        <div class="chart-pagination text-center mt-2" id="solicitantes-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="mb-0">Responsaveis em destaque</h3>
                        <small class="text-muted">Equipe de vendas</small>
                    </div>
                    <div class="card-block">
                        <div id="chart-responsaveis" class="chart-donut"></div>
                        <div id="legend-responsaveis" class="chart-legend mt-2"></div>
                        <div class="chart-pagination text-center mt-2" id="responsaveis-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            <!-- Resumo de Valores -->
            <div class="col-lg-4 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">💰 Resumo de Valores</h5>
                        <small class="text-muted">Valores do período</small>
                    </div>
                    <div class="card-block">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">💵 Dinheiro Limpo:</span>
                            <strong class="text-success">{{ $formatCurrency($totalLimpo) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">🩸 Dinheiro Sujo:</span>
                            <strong class="text-danger">{{ $formatCurrency($totalSujo) }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="font-weight-semibold">📊 Total Geral:</span>
                            <strong class="h5 text-dark mb-0">{{ $formatCurrency($totalLimpo + $totalSujo) }}</strong>
                        </div>

                        @if ($totalLimpo + $totalSujo > 0)
                            <div class="mt-3">
                                <small class="text-muted d-block">Distribuição:</small>
                                <div class="progress mt-1" style="height: 6px;">
                                    @php
                                        $total = $totalLimpo + $totalSujo;
                                        $percentLimpo = $total > 0 ? ($totalLimpo / $total) * 100 : 0;
                                        $percentSujo = $total > 0 ? ($totalSujo / $total) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $percentLimpo }}%"></div>
                                    <div class="progress-bar bg-danger" style="width: {{ $percentSujo }}%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                    <small class="text-success">{{ number_format($percentLimpo, 1) }}% Limpo</small>
                                    <small class="text-danger">{{ number_format($percentSujo, 1) }}% Sujo</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estatísticas Adicionais -->
            <div class="col-lg-8 mb-3">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">📈 Estatísticas do Período</h5>
                        <small class="text-muted">Análise detalhada</small>
                    </div>
                    <div class="card-block">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Performance</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Total de Vendas:</span>
                                        <strong>{{ $formatNumber($totais['total_vendas'] ?? 0) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Ticket Médio:</span>
                                        <strong>{{ $formatCurrency($totais['ticket_medio'] ?? 0) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Faturamento:</span>
                                        <strong>{{ $formatCurrency($totais['faturamento_total'] ?? 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Período</h6>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Data Início:</span>
                                        <strong>{{ optional($periodo['inicio'])->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Data Fim:</span>
                                        <strong>{{ optional($periodo['fim'])->format('d/m/Y') }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Dias Analisados:</span>
                                        <strong>
                                            @if ($periodo['inicio'] && $periodo['fim'])
                                                {{ $periodo['inicio']->diffInDays($periodo['fim']) + 1 }}
                                            @else
                                                -
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabela Detalhada -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-0">📋 Detalhes Diários</h3>
                <small class="text-muted">Resultados paginados</small>
            </div>
            <div class="card-block p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th><i class="ti-calendar"></i> Data</th>
                                <th class="text-center"><i class="ti-shopping-cart"></i> Vendas</th>
                                <th class="text-right"><i class="ti-money"></i> Limpo</th>
                                <th class="text-right"><i class="ti-heart"></i> Sujo</th>
                                <th class="text-right"><i class="ti-stats-up"></i> Total</th>
                            </tr>
                        </thead>
                        <tbody id="daily-table-body">
                            <!-- Dados serão carregados via JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="chart-pagination text-center mt-3 mb-3" id="daily-table-pagination"></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('assets/js/raphael/raphael.min.js') }}"></script>
    <script src="{{ asset('assets/js/morris.js/morris.js') }}"></script>
    <style>
        .chart-block {
            height: 320px;
            position: relative;
        }

        .chart-donut {
            height: 260px;
            position: relative;
        }

        .chart-legend {
            max-height: 160px;
            overflow-y: auto;
            font-size: 0.8rem;
        }

        .chart-legend span {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 6px;
            border-radius: 2px;
        }

        .chart-pagination .btn:disabled {
            cursor: not-allowed;
        }

        /* Melhorias visuais */
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .display-4 {
            font-size: 2.2rem;
            line-height: 1.2;
        }

        .progress {
            border-radius: 10px;
            overflow: hidden;
        }

        .table th {
            border-top: none;
            font-weight: 600;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.9rem;
        }

        .thead-dark th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }

        .card-block {
            padding: 1.5rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Ícones nos cards de resumo */
        .card-block i {
            transition: transform 0.3s ease;
        }

        .card:hover .card-block i {
            transform: scale(1.1);
        }
    </style>
    <script>
        (function() {
            console.log('Iniciando script dos gráficos');

            if (typeof Morris === 'undefined') {
                console.error('Biblioteca Morris não encontrada - verifique se os arquivos JS estão carregando');
                alert(
                    'Erro: Biblioteca Morris não encontrada. Verifique se os arquivos JavaScript estão sendo carregados corretamente.'
                );
                return;
            }

            console.log('Morris carregado com sucesso');

            const filters = {
                inicio: document.getElementById('data_inicio') ? document.getElementById('data_inicio').value : '',
                fim: document.getElementById('data_fim') ? document.getElementById('data_fim').value : ''
            };

            const urls = {
                daily: "{{ route('venda.fila.historico.series.diarias') }}",
                weekly: "{{ route('venda.fila.historico.series.semanais') }}",
                monthly: "{{ route('venda.fila.historico.series.mensais') }}",
                ranking: {
                    organizacoes: "{{ route('venda.fila.historico.ranking', ['tipo' => 'organizacoes']) }}",
                    solicitantes: "{{ route('venda.fila.historico.ranking', ['tipo' => 'solicitantes']) }}",
                    responsaveis: "{{ route('venda.fila.historico.ranking', ['tipo' => 'responsaveis']) }}",
                },
            };

            const charts = {
                daily: null,
                weekly: null,
                monthly: null,
            };

            const rankingState = {
                organizacoes: {
                    element: 'chart-organizacoes',
                    legend: 'legend-organizacoes',
                    pagination: 'organizacoes-pagination',
                    colors: ['#1abc9c', '#16a085', '#2ecc71', '#27ae60', '#f1c40f', '#f39c12', '#e67e22',
                        '#d35400'
                    ],
                    perPage: 8,
                    page: 1,
                    lastPage: 1,
                    chart: null,
                },
                solicitantes: {
                    element: 'chart-solicitantes',
                    legend: 'legend-solicitantes',
                    pagination: 'solicitantes-pagination',
                    colors: ['#3498db', '#2980b9', '#9b59b6', '#8e44ad', '#e74c3c', '#c0392b', '#2c3e50',
                        '#34495e'
                    ],
                    perPage: 8,
                    page: 1,
                    lastPage: 1,
                    chart: null,
                },
                responsaveis: {
                    element: 'chart-responsaveis',
                    legend: 'legend-responsaveis',
                    pagination: 'responsaveis-pagination',
                    colors: ['#1abc9c', '#16a085', '#2ecc71', '#27ae60', '#f1c40f', '#f39c12', '#e67e22',
                        '#d35400'
                    ],
                    perPage: 8,
                    page: 1,
                    lastPage: 1,
                    chart: null,
                },
            };

            const mixTipo = @json($mixTipo);
            const initialRankings = {
                organizacoes: @json($donutOrganizacoes),
                solicitantes: @json($donutSolicitantes),
                responsaveis: @json($donutResponsaveis),
            };

            function buildParams(extra) {
                const params = new URLSearchParams();
                if (filters.inicio) params.append('data_inicio', filters.inicio);
                if (filters.fim) params.append('data_fim', filters.fim);
                Object.entries(extra || {}).forEach(([key, value]) => {
                    if (value !== undefined && value !== null && value !== '') {
                        params.append(key, value);
                    }
                });
                return params;
            }

            async function fetchJson(url, extra) {
                const params = buildParams(extra);
                const fullUrl = params.toString() ? `${url}?${params.toString()}` : url;
                console.log('Fazendo requisição para:', fullUrl);

                const response = await fetch(fullUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    const message = await response.text();
                    console.error('Erro na requisição:', response.status, message);
                    throw new Error(message || 'Falha ao carregar dados.');
                }

                const data = await response.json();
                console.log('Dados recebidos:', data);
                return data;
            }

            function setMessage(elementId, message) {
                const container = document.getElementById(elementId);
                if (container) {
                    const isLoading = message.includes('Carregando');
                    const icon = isLoading ? '<i class="ti-reload fa-spin mr-2"></i>' :
                        '<i class="ti-info-alt mr-2"></i>';
                    container.innerHTML = `<div class="text-center text-muted py-4">${icon}${message}</div>`;
                }
            }

            function buildLegend(containerId, data, colors) {
                const container = document.getElementById(containerId);
                if (!container) {
                    console.error(`Container de legenda ${containerId} não encontrado`);
                    return;
                }

                container.innerHTML = '';

                if (!data || !data.length) {
                    container.innerHTML = '<p class="text-muted mb-0 text-center">Sem dados suficientes.</p>';
                    return;
                }

                console.log(`Construindo legenda para ${containerId} com ${data.length} itens`);

                data.forEach((item, index) => {
                    if (!item.label || !item.value) return;

                    const wrapper = document.createElement('div');
                    wrapper.className = 'd-flex align-items-center mb-1';

                    const marker = document.createElement('span');
                    marker.style.backgroundColor = colors[index % colors.length];
                    marker.style.width = '12px';
                    marker.style.height = '12px';
                    marker.style.display = 'inline-block';
                    marker.style.marginRight = '6px';
                    marker.style.borderRadius = '2px';
                    wrapper.appendChild(marker);

                    const text = document.createElement('small');
                    text.className = 'text-truncate';
                    text.style.flex = '1';
                    text.textContent = `${item.label} (${formatCurrency(item.value)})`;
                    wrapper.appendChild(text);

                    container.appendChild(wrapper);
                });
            }

            function updatePagination(containerId, stateObj, callback) {
                const container = document.getElementById(containerId);
                if (!container) return;

                if (!stateObj.lastPage || stateObj.lastPage <= 1) {
                    container.innerHTML = '';
                    return;
                }

                container.innerHTML = `
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-secondary" data-action="prev" ${stateObj.page <= 1 ? 'disabled' : ''}>Anterior</button>
                        <span class="btn btn-outline-light disabled">Pagina ${stateObj.page} de ${stateObj.lastPage}</span>
                        <button type="button" class="btn btn-outline-secondary" data-action="next" ${stateObj.page >= stateObj.lastPage ? 'disabled' : ''}>Proxima</button>
                    </div>
                `;

                Array.from(container.querySelectorAll('button[data-action]')).forEach((button) => {
                    button.addEventListener('click', () => {
                        const action = button.getAttribute('data-action');
                        if (action === 'prev' && stateObj.page > 1) {
                            callback(stateObj.page - 1);
                        }
                        if (action === 'next' && stateObj.page < stateObj.lastPage) {
                            callback(stateObj.page + 1);
                        }
                    });
                });
            }

            function formatCurrency(value) {
                const num = Number(value || 0);
                // Se é número inteiro, não mostrar casas decimais
                if (num === Math.floor(num)) {
                    return 'R$ ' + num.toLocaleString('pt-BR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0,
                    });
                } else {
                    // Se tem decimais, mostrar 2 casas
                    return 'R$ ' + num.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                    });
                }
            }

            function renderBarChart(elementId, stateKey, data) {
                const container = document.getElementById(elementId);
                if (!container) return;

                if (!data.length) {
                    // Não mostrar mensagem, deixar área vazia
                    container.innerHTML = '';
                    charts[stateKey] = null;
                    return;
                }

                if (!charts[stateKey]) {
                    charts[stateKey] = new Morris.Bar({
                        element: elementId,
                        data,
                        xkey: 'label',
                        ykeys: ['limpo', 'sujo'],
                        labels: ['Limpo', 'Sujo'],
                        stacked: true,
                        barColors: ['#1abc9c', '#e74c3c'],
                        hideHover: 'auto',
                        resize: true,
                        xLabelAngle: data.length > 10 ? 45 : 0,
                    });
                } else {
                    charts[stateKey].setData(data);
                }
            }

            function renderLineChart(elementId, stateKey, data, color) {
                const container = document.getElementById(elementId);
                if (!container) return;

                if (!data.length) {
                    // Não mostrar mensagem, deixar área vazia
                    container.innerHTML = '';
                    charts[stateKey] = null;
                    return;
                }

                if (!charts[stateKey]) {
                    charts[stateKey] = new Morris.Line({
                        element: elementId,
                        data,
                        xkey: 'label',
                        ykeys: ['total'],
                        labels: ['Faturamento'],
                        lineColors: [color],
                        lineWidth: 3,
                        pointFillColors: ['#ffffff'],
                        pointStrokeColors: [color],
                        hideHover: 'auto',
                        parseTime: false,
                        resize: true,
                    });
                } else {
                    charts[stateKey].setData(data);
                }
            }

            function renderAreaChart(elementId, stateKey, data, color) {
                const container = document.getElementById(elementId);
                if (!container) return;

                if (!data.length) {
                    // Não mostrar mensagem, deixar área vazia
                    container.innerHTML = '';
                    charts[stateKey] = null;
                    return;
                }

                if (!charts[stateKey]) {
                    charts[stateKey] = new Morris.Area({
                        element: elementId,
                        data,
                        xkey: 'label',
                        ykeys: ['total'],
                        labels: ['Faturamento'],
                        lineColors: [color],
                        fillOpacity: 0.2,
                        behaveLikeLine: true,
                        hideHover: 'auto',
                        pointSize: 3,
                        parseTime: false,
                        resize: true,
                    });
                } else {
                    charts[stateKey].setData(data);
                }
            }

            function renderDonutChart(stateKey, data, forceRender = false) {
                const cfg = rankingState[stateKey];
                const container = document.getElementById(cfg.element);
                if (!container) {
                    console.error(`Container ${cfg.element} não encontrado para ${stateKey}`);
                    return;
                }

                console.log(`Renderizando gráfico donut ${stateKey} com dados:`, data, `(force: ${forceRender})`);

                // Se não temos dados válidos e já existe um gráfico, não fazer nada (a menos que seja forçado)
                if ((!data || !data.length) && cfg.chart && !forceRender) {
                    console.log(`Mantendo gráfico existente para ${stateKey} (sem dados válidos)`);
                    return;
                }

                if (!data || !data.length) {
                    console.log(`Sem dados para ${stateKey}`);
                    // Não mostrar mensagem, deixar área vazia
                    cfg.element && (document.getElementById(cfg.element).innerHTML = '');
                    cfg.chart = null;
                    cfg.page = 1;
                    cfg.lastPage = 1;
                    buildLegend(cfg.legend, [], cfg.colors);
                    updatePagination(cfg.pagination, cfg, () => {});
                    return;
                }

                // Filtrar dados válidos (com value > 0)
                const validData = data.filter(item => item.value && item.value > 0);
                if (!validData.length && cfg.chart && !forceRender) {
                    console.log(`Mantendo gráfico existente para ${stateKey} (dados inválidos)`);
                    return;
                }

                if (!validData.length) {
                    console.log(`Nenhum dado válido para ${stateKey}`);
                    // Não mostrar mensagem, deixar área vazia
                    cfg.element && (document.getElementById(cfg.element).innerHTML = '');
                    cfg.chart = null;
                    buildLegend(cfg.legend, [], cfg.colors);
                    updatePagination(cfg.pagination, cfg, () => {});
                    return;
                }

                try {
                    if (!cfg.chart) {
                        console.log(`Criando novo gráfico donut para ${stateKey}`);
                        cfg.chart = new Morris.Donut({
                            element: cfg.element,
                            data: validData,
                            colors: cfg.colors,
                            resize: true,
                            formatter: function(value, data) {
                                return formatCurrency(value);
                            }
                        });
                    } else {
                        console.log(`Atualizando gráfico donut existente para ${stateKey}`);
                        cfg.chart.setData(validData);
                    }

                    buildLegend(cfg.legend, validData, cfg.colors);
                } catch (error) {
                    console.error(`Erro ao renderizar gráfico ${stateKey}:`, error);
                    setMessage(cfg.element, 'Erro ao carregar gráfico.');
                }
            }

            function renderTipoDonut(data) {
                const container = document.getElementById('chart-tipo');
                if (!container) return;

                if (!data.length) {
                    // Não mostrar mensagem, deixar área vazia
                    container.innerHTML = '';
                    buildLegend('legend-tipo', [], ['#1abc9c', '#e74c3c']);
                    return;
                }

                const chart = new Morris.Donut({
                    element: 'chart-tipo',
                    data,
                    colors: ['#1abc9c', '#e74c3c'],
                    resize: true,
                });
                buildLegend('legend-tipo', data, ['#1abc9c', '#e74c3c']);
            }

            function setDailyTableMessage(message) {
                const tbody = document.getElementById('daily-table-body');
                if (!tbody) return;
                const isLoading = message.includes('Carregando');
                const icon = isLoading ? '<i class="ti-reload fa-spin mr-2"></i>' : '<i class="ti-info-alt mr-2"></i>';
                tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted py-4">${icon}${message}</td></tr>`;
            }

            function renderDailyTable(rows) {
                const tbody = document.getElementById('daily-table-body');
                if (!tbody) return;

                if (!rows || !rows.length) {
                    // Não mostrar mensagem, deixar tabela vazia
                    tbody.innerHTML = '';
                    return;
                }

                tbody.innerHTML = '';

                rows.forEach((row) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${row.referencia || '-'}</td>
                        <td class="text-center">${Number(row.total_vendas || 0).toLocaleString('pt-BR')}</td>
                        <td class="text-right">${formatCurrency(row.total_limpo || 0)}</td>
                        <td class="text-right">${formatCurrency(row.total_sujo || 0)}</td>
                        <td class="text-right">${formatCurrency(row.faturamento || 0)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }

            async function loadDaily(page) {
                try {
                    const response = await fetchJson(urls.daily, {
                        per_page: 30,
                        page,
                    });

                    const data = (response.data || []).map((item) => ({
                        label: item.referencia,
                        limpo: Number(item.total_limpo || 0),
                        sujo: Number(item.total_sujo || 0),
                    }));

                    renderBarChart('chart-daily', 'daily', data);
                    renderDailyTable(response.data || []);

                    const meta = response.meta || {
                        page: page,
                        last_page: 1
                    };
                    const stateObj = {
                        page: meta.page || page,
                        lastPage: meta.last_page || 1
                    };
                    updatePagination('daily-pagination', stateObj, loadDaily);
                    updatePagination('daily-table-pagination', stateObj, loadDaily);
                } catch (error) {
                    console.error('Erro ao carregar dados diários:', error);
                    // Em caso de erro, deixar áreas vazias ao invés de mostrar mensagem
                }
            }

            async function loadWeekly(page) {
                try {
                    const response = await fetchJson(urls.weekly, {
                        per_page: 12,
                        page,
                    });

                    const data = (response.data || []).map((item) => ({
                        label: item.referencia,
                        total: Number(item.faturamento || 0),
                    }));

                    renderLineChart('chart-weekly', 'weekly', data, '#3498db');

                    const meta = response.meta || {
                        page: page,
                        last_page: 1
                    };
                    updatePagination('weekly-pagination', {
                        page: meta.page || page,
                        lastPage: meta.last_page || 1
                    }, loadWeekly);
                } catch (error) {
                    console.error('Erro ao carregar dados semanais:', error);
                    // Em caso de erro, deixar área vazia ao invés de mostrar mensagem
                }
            }

            async function loadMonthly(page) {
                try {
                    const response = await fetchJson(urls.monthly, {
                        per_page: 12,
                        page,
                    });

                    const data = (response.data || []).map((item) => ({
                        label: item.referencia,
                        total: Number(item.faturamento || 0),
                    }));

                    renderAreaChart('chart-monthly', 'monthly', data, '#9b59b6');

                    const meta = response.meta || {
                        page: page,
                        last_page: 1
                    };
                    updatePagination('monthly-pagination', {
                        page: meta.page || page,
                        lastPage: meta.last_page || 1
                    }, loadMonthly);
                } catch (error) {
                    console.error('Erro ao carregar dados mensais:', error);
                    // Em caso de erro, deixar área vazia ao invés de mostrar mensagem
                }
            }

            async function loadRanking(tipo, page) {
                const cfg = rankingState[tipo];
                if (!cfg) {
                    console.error(`Configuração não encontrada para tipo: ${tipo}`);
                    return;
                }

                // Se já existe um gráfico renderizado, não substituir por dados vazios
                const hasExistingChart = cfg.chart !== null;

                try {
                    console.log(
                        `Carregando ranking ${tipo}, página ${page} (gráfico existente: ${hasExistingChart})`);

                    const response = await fetchJson(urls.ranking[tipo], {
                        per_page: cfg.perPage,
                        page,
                    });

                    console.log(`Resposta do ranking ${tipo}:`, response);

                    const data = (response.data || []).map((item) => {
                        let label;
                        if (tipo === 'responsaveis') {
                            label = item.responsavel || 'Não informado';
                        } else {
                            label = item.nome || 'Não informado';
                        }

                        return {
                            label: label,
                            value: Number(item.faturamento || 0),
                        };
                    }).filter(item => item.value > 0); // Filtrar apenas valores válidos

                    console.log(`Dados processados para ${tipo}:`, data);

                    // Só atualizar se temos dados válidos OU se não há gráfico existente
                    if (data.length > 0 || !hasExistingChart) {
                        cfg.page = response.meta?.page || page;
                        cfg.lastPage = response.meta?.last_page || 1;

                        renderDonutChart(tipo, data);
                        updatePagination(cfg.pagination, cfg, (nextPage) => loadRanking(tipo, nextPage));
                    } else {
                        console.log(`Mantendo gráfico existente para ${tipo} (dados AJAX vazios)`);
                    }
                } catch (error) {
                    console.error(`Erro ao carregar ranking ${tipo}:`, error);
                    // Em caso de erro, deixar área vazia ou manter gráfico existente
                }
            }

            document.addEventListener('DOMContentLoaded', function() {
                console.log('DOM carregado, iniciando renderização dos gráficos');
                console.log('Dados iniciais:', {
                    mixTipo,
                    initialRankings
                });

                // Verificar se os elementos existem no DOM
                ['chart-organizacoes', 'chart-solicitantes', 'chart-responsaveis'].forEach(id => {
                    const element = document.getElementById(id);
                    console.log(`Elemento ${id}:`, element ? 'encontrado' : 'NÃO ENCONTRADO');
                });

                renderTipoDonut(mixTipo);

                console.log('Renderizando gráficos iniciais...');
                if (initialRankings.organizacoes && initialRankings.organizacoes.length) {
                    console.log('Renderizando organizações iniciais:', initialRankings.organizacoes);
                    renderDonutChart('organizacoes', initialRankings.organizacoes, true);
                } else {
                    console.log('Sem dados iniciais para organizações');
                }

                if (initialRankings.solicitantes && initialRankings.solicitantes.length) {
                    console.log('Renderizando solicitantes iniciais:', initialRankings.solicitantes);
                    renderDonutChart('solicitantes', initialRankings.solicitantes, true);
                } else {
                    console.log('Sem dados iniciais para solicitantes');
                }

                if (initialRankings.responsaveis && initialRankings.responsaveis.length) {
                    console.log('Renderizando responsáveis iniciais:', initialRankings.responsaveis);
                    renderDonutChart('responsaveis', initialRankings.responsaveis, true);
                } else {
                    console.log('Sem dados iniciais para responsáveis');
                }

                console.log('Carregando dados via AJAX...');
                loadDaily(1);
                loadWeekly(1);
                loadMonthly(1);

                // Só carregar via AJAX se não temos dados iniciais, ou após um delay maior para debug
                if (!initialRankings.organizacoes || !initialRankings.organizacoes.length) {
                    console.log('Carregando organizações via AJAX (sem dados iniciais)');
                    setTimeout(() => loadRanking('organizacoes', 1), 1000);
                } else {
                    console.log('Usando dados iniciais para organizações, AJAX desabilitado temporariamente');
                }

                if (!initialRankings.solicitantes || !initialRankings.solicitantes.length) {
                    console.log('Carregando solicitantes via AJAX (sem dados iniciais)');
                    setTimeout(() => loadRanking('solicitantes', 1), 1200);
                } else {
                    console.log('Usando dados iniciais para solicitantes, AJAX desabilitado temporariamente');
                }

                if (!initialRankings.responsaveis || !initialRankings.responsaveis.length) {
                    console.log('Carregando responsáveis via AJAX (sem dados iniciais)');
                    setTimeout(() => loadRanking('responsaveis', 1), 1400);
                } else {
                    console.log('Usando dados iniciais para responsáveis, AJAX desabilitado temporariamente');
                }
            });
        })();
    </script>
@endsection
