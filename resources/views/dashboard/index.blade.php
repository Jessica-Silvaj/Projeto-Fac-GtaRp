@extends('layouts.master', ['titulo' => 'Painel', 'subtitulo' => 'Visão geral'])

@section('conteudo')
    <div class="col-sm-12">

        <style>
            .card-elevated {
                border: 0;
                box-shadow: 0 10px 22px rgba(0, 0, 0, .06), 0 4px 6px rgba(0, 0, 0, .04);
                border-radius: 1rem;
            }

            .soft {
                color: var(--text-muted, #9d711d);
            }

            .kpi {
                border-radius: 1rem;
                overflow: hidden;
                position: relative;
                height: 100%;
            }

            .kpi .value {
                font-size: clamp(1.4rem, 1.2rem + 1vw, 2rem);
                font-weight: 800;
            }

            .chip {
                border-radius: 999px;
                padding: .35rem .7rem;
                font-size: .8rem;
                background: var(--chip-bg, rgba(0, 0, 0, .06));
                color: var(--chip-fg, inherit);
            }

            .chip.success {
                background: var(--success-bg, rgba(16, 185, 129, .12));
                color: var(--success-fg, #059669);
            }

            .chip.danger {
                background: var(--danger-bg, rgba(239, 68, 68, .12));
                color: var(--danger-fg, #dc2626);
            }

            .chip.info {
                background: var(--info-bg, rgba(99, 102, 241, .12));
                color: var(--info-fg, #4f46e5);
            }

            .row-tight>[class*='col-'] {
                padding-left: .6rem;
                padding-right: .6rem;
            }
        </style>

        {{-- =========================
         LINHA 1: 3 KPIs
    ========================== --}}
        @php
            $row1 = $cards_row1 ?? (isset($cards) ? array_slice($cards, 0, 3) : []);
        @endphp
        <div class="row row-tight">
            @foreach ($row1 as $card)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-elevated kpi h-100">
                        <div class="card-body d-flex align-items-start justify-content-between">
                            <div>
                                <div class="soft mb-1">{{ $card['label'] ?? '—' }}</div>
                                <div class="value mb-1" style="color:black">{{ $card['value'] ?? 0 }}</div>
                                @if (!empty($card['description']))
                                    <small class="soft">{{ $card['description'] }}</small>
                                @endif
                            </div>
                            @if (!empty($card['icon']))
                                <span class="d-inline-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;border-radius:14px;background:rgba(0,0,0,.06)">
                                    <i class="{{ $card['icon'] }} soft f-24"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- =========================
         LINHA 2: 3 KPIs (usa $cards_row2 ou defaults)
    ========================== --}}
        @php
            $row2 =
                $cards_row2 ??
                (isset($cards)
                    ? array_slice($cards, 3, 3)
                    : [
                        ['label' => 'Usuários Ativos', 'value' => $usuariosAtivos ?? 0, 'icon' => 'feather icon-users'],
                        ['label' => 'Baús Criados', 'value' => $bausCriados ?? 0, 'icon' => 'feather icon-archive'],
                        [
                            'label' => 'Itens Movimentados',
                            'value' => $itensMovimentados ?? 0,
                            'icon' => 'feather icon-box',
                        ],
                    ]);
        @endphp
        <div class="row row-tight">
            @foreach ($row2 as $card)
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card card-elevated kpi h-100">
                        <div class="card-body d-flex align-items-start justify-content-between">
                            <div>
                                <div class="soft mb-1">{{ $card['label'] ?? '—' }}</div>
                                <div class="value mb-1" style="color:black">{{ $card['value'] ?? 0 }}</div>
                                @if (!empty($card['description']))
                                    <small class="soft">{{ $card['description'] }}</small>
                                @endif
                            </div>
                            @if (!empty($card['icon']))
                                <span class="d-inline-flex align-items-center justify-content-center"
                                    style="width:44px;height:44px;border-radius:14px;background:rgba(0,0,0,.06)">
                                    <i class="{{ $card['icon'] }} soft f-24"></i>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- =========================
         LINHA 3: GRÁFICOS LADO A LADO
    ========================== --}}
        <div class="row row-tight">
            <div class="col-xl-6 mb-4">
                <div class="card card-elevated h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">Solicitações no Discord</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartSolicitacoes" height="160"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 mb-4">
                <div class="card card-elevated h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3 class="mb-0">Resumo de Lançamentos</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartLancamentos" height="160"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function cssColorOf(className, fallback) {
            const el = document.createElement('span');
            el.className = className;
            el.style.display = 'none';
            document.body.appendChild(el);
            const color = getComputedStyle(el).color;
            el.remove();
            return color || fallback;
        }

        // ======== Solicitações (Doughnut) ========
        const resumoSolic = @json((object) ($solicitacaoResumo ?? []));
        const labelsSolic = Object.keys(resumoSolic);
        const dataSolic = Object.values(resumoSolic);
        const palette = [
            cssColorOf('text-primary', '#3b82f6'),
            cssColorOf('text-success', '#10b981'),
            cssColorOf('text-danger', '#ef4444'),
            cssColorOf('text-info', '#6366f1'),
            cssColorOf('text-warning', '#f59e0b'),
            cssColorOf('text-secondary', '#6b7280')
        ];
        const ctx1 = document.getElementById('chartSolicitacoes');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'doughnut',
                data: {
                    labels: labelsSolic,
                    datasets: [{
                        data: dataSolic,
                        backgroundColor: labelsSolic.map((_, i) => palette[i % palette.length]),
                        borderWidth: 0,
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '60%'
                }
            });
        }

        // ======== Lançamentos (Bar) ========
        const lr = @json((object) ($lancamentoResumo ?? []));
        const entradas = Number(lr.entradas ?? 0);
        const saidas = Number(lr.saidas ?? 0);
        const total = Number(lr.total ?? (entradas + saidas));
        const ctx2 = document.getElementById('chartLancamentos');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: ['Entradas', 'Saídas', 'Total'],
                    datasets: [{
                        label: 'Movimentações',
                        data: [entradas, saidas, total],
                        backgroundColor: [
                            cssColorOf('text-success', '#10b981'),
                            cssColorOf('text-danger', '#ef4444'),
                            cssColorOf('text-info', '#6366f1')
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endsection
