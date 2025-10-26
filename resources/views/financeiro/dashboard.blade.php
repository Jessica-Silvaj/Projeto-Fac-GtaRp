@extends('layouts.master')

@section('title', 'Dashboard Financeiro')

@section('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .metric-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .metric-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .metric-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .metric-card .metric-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 3rem;
            opacity: 0.3;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0.5rem;
        }

        .metric-change {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }

        .metric-change.positive {
            color: #4ade80;
        }

        .metric-change.negative {
            color: #f87171;
        }

        .metric-change.neutral {
            color: #94a3b8;
        }

        .chart-container {
            position: relative;
            height: 400px;
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .ranking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid #667eea;
            transition: transform 0.2s ease;
        }

        .ranking-item:hover {
            transform: translateX(5px);
        }

        .ranking-position {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }



        .alert-item {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid;
        }

        .alert-item.warning {
            background: #fffbeb;
            border-color: #f59e0b;
            color: #92400e;
        }

        .alert-item.info {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1e40af;
        }

        .refresh-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            border-radius: 15px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f4f6;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Botão de Refresh -->
        <button class="btn btn-outline-primary refresh-btn" onclick="refreshDashboard()" title="Atualizar dados">
            <i class="fas fa-sync-alt" id="refreshIcon"></i>
        </button>

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="display-4 fw-bold text-primary">
                            <i class="fas fa-chart-line me-3"></i>Dashboard Financeiro
                        </h1>
                        <p class="lead text-muted mb-0">Análise completa dos repasses e métricas financeiras</p>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">Última atualização: </small>
                        <strong id="lastUpdate">{{ date('d/m/Y H:i:s') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Métricas Principais -->
        <div class="row mb-4" id="metricsContainer">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card metric-card">
                    <div class="metric-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metric-value" id="totalRepasses">{{ $kpis['mes_atual']['repasses'] }}</div>
                    <div class="metric-label">Repasses este Mês</div>
                    <div class="metric-change" id="changeRepasses">
                        @if ($kpis['crescimento']['repasses'] > 0)
                            <i class="fas fa-arrow-up me-1"></i>
                            <span class="positive">+{{ $kpis['crescimento']['repasses'] }}%</span>
                        @elseif($kpis['crescimento']['repasses'] < 0)
                            <i class="fas fa-arrow-down me-1"></i>
                            <span class="negative">{{ $kpis['crescimento']['repasses'] }}%</span>
                        @else
                            <i class="fas fa-minus me-1"></i>
                            <span class="neutral">0%</span>
                        @endif
                        <small class="ms-1 opacity-75">vs mês anterior</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card metric-card success">
                    <div class="metric-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="metric-value" id="valorTotal">R$
                        {{ number_format($kpis['mes_atual']['valor'], 0, ',', '.') }}</div>
                    <div class="metric-label">Total Repassado</div>
                    <div class="metric-change" id="changeValor">
                        @if ($kpis['crescimento']['valor'] > 0)
                            <i class="fas fa-arrow-up me-1"></i>
                            <span class="positive">+{{ $kpis['crescimento']['valor'] }}%</span>
                        @elseif($kpis['crescimento']['valor'] < 0)
                            <i class="fas fa-arrow-down me-1"></i>
                            <span class="negative">{{ $kpis['crescimento']['valor'] }}%</span>
                        @else
                            <i class="fas fa-minus me-1"></i>
                            <span class="neutral">0%</span>
                        @endif
                        <small class="ms-1 opacity-75">vs mês anterior</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card metric-card warning">
                    <div class="metric-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="metric-value" id="vendedoresAtivos">{{ $kpis['mes_atual']['vendedores'] }}</div>
                    <div class="metric-label">Vendedores Ativos</div>
                    <div class="metric-change neutral">
                        <i class="fas fa-user-check me-1"></i>
                        <span>Este mês</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card metric-card info">
                    <div class="metric-icon">
                        <i class="fas fa-calculator"></i>
                    </div>
                    <div class="metric-value" id="mediaRepasse">R$
                        {{ number_format($kpis['mes_atual']['media'], 0, ',', '.') }}</div>
                    <div class="metric-label">Média por Repasse</div>
                    <div class="metric-change neutral">
                        <i class="fas fa-chart-bar me-1"></i>
                        <span>Ticket médio</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos e Análises -->
        <div class="row mb-4">
            <!-- Evolução Diária -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-area text-primary me-2"></i>
                            Evolução Diária do Mês
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="evolucaoChart"></canvas>
                            <div class="loading-overlay d-none" id="evolucaoLoading">
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribuição Limpo vs Sujo -->
            <div class="col-lg-4 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-pie text-success me-2"></i>
                            Distribuição de Valores
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="distribuicaoChart"></canvas>
                            <div class="loading-overlay d-none" id="distribuicaoLoading">
                                <div class="spinner"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-circle text-success me-1"></i>Limpo:</span>
                                <strong>{{ $distribuicao['percentual_limpo'] }}%</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span><i class="fas fa-circle text-danger me-1"></i>Sujo:</span>
                                <strong>{{ $distribuicao['percentual_sujo'] }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ranking e Atividades -->
        <div class="row mb-4">
            <!-- Ranking de Vendedores -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-trophy text-warning me-2"></i>
                            Top 10 Vendedores (Este Mês)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="rankingContainer">
                            @foreach ($rankingVendedores as $index => $vendedor)
                                <div class="ranking-item">
                                    <div class="d-flex align-items-center">
                                        <div class="ranking-position">{{ $index + 1 }}</div>
                                        <div class="ms-3">
                                            <div class="fw-bold">{{ $vendedor->vendedor_nome }}</div>
                                            <small class="text-muted">{{ $vendedor->total_repasses }} repasses</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success">R$
                                            {{ number_format($vendedor->total_geral, 0, ',', '.') }}</div>
                                        <small class="text-muted">Último: {{ $vendedor->ultimo_repasse }}</small>
                                    </div>
                                </div>
                            @endforeach

                            @if (count($rankingVendedores) == 0)
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle fa-2x mb-3"></i>
                                    <p>Nenhum repasse encontrado neste mês.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <!-- Alertas -->
        @if (count($alertas) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                Alertas e Notificações
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="alertasContainer">
                                @foreach ($alertas as $alerta)
                                    <div class="alert-item {{ $alerta['tipo'] }}">
                                        <div class="d-flex align-items-center">
                                            <i class="{{ $alerta['icone'] }} me-3"></i>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $alerta['titulo'] }}</div>
                                                <div>{{ $alerta['mensagem'] }}</div>
                                                @if (isset($alerta['data']))
                                                    <small class="text-muted">{{ $alerta['data'] }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Links Rápidos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-link text-primary me-2"></i>
                            Acesso Rápido
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="{{ route('financeiro.index') }}" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-money-bill-wave me-2"></i>
                                    Controle Financeiro
                                </a>
                            </div>
                            <div class="col-lg-3 col-md-6 mb-3">
                                <a href="{{ route('financeiro.relatorio') }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-file-alt me-2"></i>
                                    Relatórios
                                </a>
                            </div>

                            <div class="col-lg-3 col-md-6 mb-3">
                                <button onclick="refreshDashboard()" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sync-alt me-2"></i>
                                    Atualizar Dados
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Variáveis globais para os gráficos
        let evolucaoChart = null;
        let distribuicaoChart = null;

        // Dados iniciais do servidor
        const initialData = {
            evolucao: @json($evolucaoDiaria),
            distribuicao: @json($distribuicao)
        };

        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();

            // Auto-refresh a cada 5 minutos
            setInterval(refreshDashboard, 5 * 60 * 1000);
        });

        function initializeCharts() {
            // Gráfico de Evolução Diária
            const evolucaoCtx = document.getElementById('evolucaoChart').getContext('2d');

            const evolucaoLabels = initialData.evolucao.map(item => item.dia);
            const evolucaoData = initialData.evolucao.map(item => parseFloat(item.total_dia) || 0);
            const evolucaoQuantidade = initialData.evolucao.map(item => parseInt(item.quantidade_repasses) || 0);

            evolucaoChart = new Chart(evolucaoCtx, {
                type: 'line',
                data: {
                    labels: evolucaoLabels,
                    datasets: [{
                            label: 'Valor Total (R$)',
                            data: evolucaoData,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Quantidade de Repasses',
                            data: evolucaoQuantidade,
                            borderColor: '#38ef7d',
                            backgroundColor: 'rgba(56, 239, 125, 0.1)',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.4,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return context.dataset.label + ': R$ ' + new Intl.NumberFormat('pt-BR')
                                            .format(context.parsed.y);
                                    } else {
                                        return context.dataset.label + ': ' + context.parsed.y + ' repasses';
                                    }
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Dias do Mês'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Valor (R$)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + new Intl.NumberFormat('pt-BR').format(value);
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Quantidade'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        }
                    }
                }
            });

            // Gráfico de Distribuição (Pizza)
            const distribuicaoCtx = document.getElementById('distribuicaoChart').getContext('2d');

            distribuicaoChart = new Chart(distribuicaoCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Valor Limpo', 'Valor Sujo'],
                    datasets: [{
                        data: [
                            parseFloat(initialData.distribuicao.limpo) || 0,
                            parseFloat(initialData.distribuicao.sujo) || 0
                        ],
                        backgroundColor: [
                            '#38ef7d',
                            '#f5576c'
                        ],
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return context.label + ': R$ ' + new Intl.NumberFormat('pt-BR').format(
                                        value) + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        function refreshDashboard() {
            const refreshIcon = document.getElementById('refreshIcon');
            refreshIcon.classList.add('fa-spin');

            // Mostrar loading nos gráficos
            showLoading('evolucaoLoading');
            showLoading('distribuicaoLoading');

            fetch('{{ route('financeiro.dashboard.api') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateMetrics(data.kpis);
                        updateCharts(data.evolucao_diaria, data.distribuicao);
                        updateRanking(data.ranking_vendedores);

                        updateAlerts(data.alertas);

                        // Atualizar timestamp
                        document.getElementById('lastUpdate').textContent = new Date().toLocaleString('pt-BR');

                        showToast('Dashboard atualizado com sucesso!', 'success');
                    } else {
                        showToast('Erro ao atualizar dashboard: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    showToast('Erro ao comunicar com o servidor', 'error');
                })
                .finally(() => {
                    refreshIcon.classList.remove('fa-spin');
                    hideLoading('evolucaoLoading');
                    hideLoading('distribuicaoLoading');
                });
        }

        function updateMetrics(kpis) {
            document.getElementById('totalRepasses').textContent = kpis.mes_atual.repasses;
            document.getElementById('valorTotal').textContent = 'R$ ' + new Intl.NumberFormat('pt-BR').format(kpis.mes_atual
                .valor);
            document.getElementById('vendedoresAtivos').textContent = kpis.mes_atual.vendedores;
            document.getElementById('mediaRepasse').textContent = 'R$ ' + new Intl.NumberFormat('pt-BR').format(kpis
                .mes_atual.media);

            // Atualizar indicadores de mudança
            updateChangeIndicator('changeRepasses', kpis.crescimento.repasses);
            updateChangeIndicator('changeValor', kpis.crescimento.valor);
        }

        function updateChangeIndicator(elementId, change) {
            const element = document.getElementById(elementId);
            const icon = element.querySelector('i');
            const span = element.querySelector('span');

            if (change > 0) {
                icon.className = 'fas fa-arrow-up me-1';
                span.className = 'positive';
                span.textContent = '+' + change + '%';
            } else if (change < 0) {
                icon.className = 'fas fa-arrow-down me-1';
                span.className = 'negative';
                span.textContent = change + '%';
            } else {
                icon.className = 'fas fa-minus me-1';
                span.className = 'neutral';
                span.textContent = '0%';
            }
        }

        function updateCharts(evolucaoData, distribuicaoData) {
            // Atualizar gráfico de evolução
            if (evolucaoChart) {
                evolucaoChart.data.labels = evolucaoData.map(item => item.dia);
                evolucaoChart.data.datasets[0].data = evolucaoData.map(item => parseFloat(item.total_dia) || 0);
                evolucaoChart.data.datasets[1].data = evolucaoData.map(item => parseInt(item.quantidade_repasses) || 0);
                evolucaoChart.update();
            }

            // Atualizar gráfico de distribuição
            if (distribuicaoChart) {
                distribuicaoChart.data.datasets[0].data = [
                    parseFloat(distribuicaoData.limpo) || 0,
                    parseFloat(distribuicaoData.sujo) || 0
                ];
                distribuicaoChart.update();
            }
        }

        function updateRanking(ranking) {
            const container = document.getElementById('rankingContainer');

            if (ranking.length === 0) {
                container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-info-circle fa-2x mb-3"></i>
                <p>Nenhum repasse encontrado neste mês.</p>
            </div>
        `;
                return;
            }

            let html = '';
            ranking.forEach((vendedor, index) => {
                html += `
            <div class="ranking-item">
                <div class="d-flex align-items-center">
                    <div class="ranking-position">${index + 1}</div>
                    <div class="ms-3">
                        <div class="fw-bold">${vendedor.vendedor_nome}</div>
                        <small class="text-muted">${vendedor.total_repasses} repasses</small>
                    </div>
                </div>
                <div class="text-end">
                    <div class="fw-bold text-success">R$ ${new Intl.NumberFormat('pt-BR').format(vendedor.total_geral)}</div>
                    <small class="text-muted">Último: ${vendedor.ultimo_repasse}</small>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }



        function updateAlerts(alertas) {
            const container = document.getElementById('alertasContainer');

            if (!container) return; // Se não há container de alertas, ignora

            if (alertas.length === 0) {
                container.closest('.card').style.display = 'none';
                return;
            }

            container.closest('.card').style.display = 'block';

            let html = '';
            alertas.forEach(alerta => {
                html += `
            <div class="alert-item ${alerta.tipo}">
                <div class="d-flex align-items-center">
                    <i class="${alerta.icone} me-3"></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${alerta.titulo}</div>
                        <div>${alerta.mensagem}</div>
                        ${alerta.data ? `<small class="text-muted">${alerta.data}</small>` : ''}
                    </div>
                </div>
            </div>
        `;
            });

            container.innerHTML = html;
        }

        function showLoading(elementId) {
            const loading = document.getElementById(elementId);
            if (loading) {
                loading.classList.remove('d-none');
            }
        }

        function hideLoading(elementId) {
            const loading = document.getElementById(elementId);
            if (loading) {
                loading.classList.add('d-none');
            }
        }

        function showToast(message, type = 'info') {
            // Implementação simples de toast - você pode usar uma biblioteca como Toastr
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                info: '#17a2b8',
                warning: '#ffc107'
            };

            const toast = document.createElement('div');
            toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        z-index: 9999;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
    `;
            toast.textContent = message;

            document.body.appendChild(toast);

            // Animar entrada
            setTimeout(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            }, 100);

            // Remover após 5 segundos
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 5000);
        }
    </script>
@endsection
