@extends('layouts.master', ['titulo' => 'Painel', 'subtitulo' => 'Visão geral'])

@section('conteudo')
    <div class="col-sm-12">
        <style>
            :root {
                --muted: #6b7280;
                --card-elev: 0 10px 30px rgba(2, 6, 23, 0.06);
            }

            /* KPIs */
            .kpi-grid {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                margin-bottom: 1rem;
            }

            .kpi-col {
                flex: 1 1 calc(33.333% - 1rem);
                min-width: 240px;
            }

            .kpi-card {
                background: #272e34;
                border-radius: 12px;
                padding: 1rem;
                display: flex;
                gap: 1rem;
                align-items: center;
                box-shadow: var(--card-elev);
                border: 1px solid rgba(15, 23, 42, 0.03);
                transition: transform .16s, box-shadow .16s;
            }

            .kpi-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 18px 40px rgba(2, 6, 23, 0.08);
            }

            .kpi-icon {
                width: 64px;
                height: 64px;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #fff;
                flex: 0 0 64px;
            }

            .kpi-body {
                flex: 1;
                min-width: 0;
            }

            .kpi-label {
                font-weight: 600;
                color: #cb9725;
                font-size: .95rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .kpi-meta {
                color: var(--muted);
                font-size: .85rem;
                margin-top: .125rem;
            }

            .kpi-value {
                font-size: 1.6rem;
                font-weight: 700;
                color: #cb9725;
                margin-top: .35rem;
            }

            .kpi-chip {
                padding: .25rem .5rem;
                border-radius: 999px;
                font-size: .78rem;
                color: #fff;
                background: #0ea5a3;
                display: inline-block;
            }

            /* Panels e listas */
            .row-equal>[class*="col-"] {
                display: flex;
            }

            .card-panel {
                display: flex;
                flex-direction: column;
                padding: 1rem;
                border-radius: 12px;
                background: #272e34;
                box-shadow: var(--card-elev);
                border: 1px solid rgba(15, 23, 42, 0.03);
                width: 100%;
            }

            .card-panel .chart-wrap {
                flex: 1 1 auto;
                display: flex;
                align-items: center;
            }

            .card-panel canvas {
                width: 100% !important;
                height: 220px !important;
                max-height: 260px;
            }

            /* Pendentes: scroll */
            .list-scroll {
                max-height: 420px;
                overflow: auto;
                padding-right: .6rem;
                margin-top: .75rem;
            }

            .list-scroll::-webkit-scrollbar {
                width: 10px;
                height: 10px;
            }

            .list-scroll::-webkit-scrollbar-track {
                background: transparent;
            }

            .list-scroll::-webkit-scrollbar-thumb {
                background: rgba(15, 23, 42, 0.08);
                border-radius: 999px;
                border: 2px solid transparent;
                background-clip: padding-box;
            }

            .list-scroll::-webkit-scrollbar-thumb:hover {
                background: rgba(15, 23, 42, 0.12);
            }

            .list-scroll {
                scrollbar-width: thin;
                scrollbar-color: rgba(15, 23, 42, 0.08) transparent;
            }

            .list-item {
                display: flex;
                gap: .75rem;
                align-items: flex-start;
                margin-bottom: .9rem;
            }

            .icon-box {
                width: 48px;
                height: 48px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 600;
                flex: 0 0 48px;
            }

            .meta {
                flex: 1;
                min-width: 0;
            }

            .meta strong {
                display: block;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .meta small {
                display: block;
                color: var(--muted);
            }

            .right {
                text-align: right;
                min-width: 0;
            }

            /* Responsividade */
            @media (max-width:991px) {
                .kpi-col {
                    flex: 1 1 calc(50% - 1rem);
                }
            }

            @media (max-width:575px) {
                .kpi-col {
                    flex: 1 1 100%;
                }

                .kpi-icon {
                    width: 56px;
                    height: 56px;
                    font-size: 20px;
                }

                .card-panel canvas {
                    height: 180px !important;
                }

                .list-scroll {
                    max-height: 260px;
                }
            }
        </style>

        {{-- KPIs --}}
        @php
            $row1 = $cards_row1 ?? (isset($cards) ? array_slice($cards, 0, 3) : []);
            $row2 = $cards_row2 ?? (isset($cards) ? array_slice($cards, 3, 3) : []);
        @endphp
        <div class="kpi-grid">
            @foreach (array_merge($row1, $row2) as $card)
                <div class="kpi-col">
                    @if (!empty($card['url']))
                        <a href="{{ $card['url'] }}" style="text-decoration:none;color:inherit;">
                    @endif
                    <div class="kpi-card">
                        <div class="kpi-icon" style="background:{!! $card['color'] ?? 'linear-gradient(135deg,#6366f1,#06b6d4)' !!};">
                            <i class="{{ $card['icon'] ?? 'ti-user' }}"></i>
                        </div>
                        <div class="kpi-body">
                            <div class="kpi-label">{{ $card['label'] ?? '' }}</div>
                            <div class="kpi-meta">{{ $card['description'] ?? '' }}</div>
                            <div class="kpi-value" data-target="{{ $card['value'] ?? 0 }}">0</div>
                        </div>
                        @if (!empty($card['url']))
                            <div style="margin-left:12px"><span class="kpi-chip">Ir</span></div>
                        @endif
                    </div>
                    @if (!empty($card['url']))
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Painéis Solicitações e Lançamentos lado a lado --}}
        <div class="row row-tight row-equal">
            <div class="col-xl-6 mb-4">
                <div class="card-panel">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                        <h5 style="margin:0;">Solicitações</h5>
                        <div>
                            @if (Route::has('bau.lancamentos.solicitacoes.index'))
                                <a href="{{ route('bau.lancamentos.solicitacoes.index') }}"
                                    class="btn btn-sm btn-outline-primary">Ver todas</a>
                            @endif
                        </div>
                    </div>

                    <div class="chart-wrap">
                        <canvas id="chartSolicitacoes" height="220"></canvas>
                    </div>

                    <hr>

                    <h6 style="margin-bottom:.5rem;">Pendentes recentes</h6>
                    <ul class="list-unstyled small-list list-scroll">
                        @forelse($solicitacoesPendentes ?? [] as $s)
                            @php
                                $assunto = $s->assunto ?? ($s->titulo ?? 'Solicitação');
                                $hora = optional($s->created_at)->format('d/m/Y H:i');
                                $usuarioNome =
                                    optional($s->usuario)->nome ?? (optional($s->usuario)->username ?? 'N/A');
                                $bg = 'linear-gradient(135deg,#f59e0b,#ef4444)';
                                $badgeBg = '#fff8ed';
                                $badgeColor = '#92400e';
                            @endphp

                            <li class="list-item">
                                <div class="icon-box" style="background: {{ $bg }};">
                                    <i class="ti-time" style="font-size:16px"></i>
                                </div>

                                <div class="meta">
                                    <strong
                                        title="{{ $assunto }}">{{ \Illuminate\Support\Str::limit($assunto, 70) }}</strong>
                                    <small>Por {{ $usuarioNome }} · <span>{{ $hora }}</span></small>

                                    @if (!empty($s->descricao ?? $s->mensagem))
                                        <div style="margin-top:.35rem;color:var(--muted);font-size:.92rem;">
                                            {{ \Illuminate\Support\Str::limit($s->descricao ?? $s->mensagem, 140) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="right">
                                    <span
                                        style="background:{{ $badgeBg }};color:{{ $badgeColor }};padding:.25rem .5rem;border-radius:.5rem;font-size:.8rem;border:1px solid rgba(0,0,0,0.03)">Pendente</span>
                                    <div class="text-muted" style="font-size:.85rem;margin-top:.35rem;">ID
                                        #{{ $s->id }}</div>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted">Nenhuma solicitação pendente.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-xl-6 mb-4">
                <div class="card-panel">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
                        <h5 style="margin:0;">Lançamentos</h5>
                        <div>
                            @if (Route::has('bau.lancamentos.index'))
                                <a href="{{ route('bau.lancamentos.index') }}" class="btn btn-sm btn-outline-primary">Ver
                                    todos</a>
                            @endif
                        </div>
                    </div>

                    <div class="chart-wrap">
                        <canvas id="chartLancamentos" height="220"></canvas>
                    </div>

                    <hr>

                    <h6 style="margin-bottom:.5rem;">Últimos lançamentos</h6>
                    <ul class="list-unstyled small-list list-scroll">
                        @forelse($ultimosLancamentos ?? [] as $l)
                            @php
                                $tipo = strtolower($l->tipo ?? ($l->movimento ?? ''));
                                $quant = $l->quantidade ?? ($l->qtd ?? ($l->quantidade_movimento ?? null));
                                $itemNome = optional($l->item)->nome ?? 'Item #' . ($l->item_id ?? '?');
                                $usuarioNome =
                                    optional($l->usuario)->nome ?? (optional($l->usuario)->username ?? 'N/A');
                                $time = optional($l->data_atribuicao ?? $l->created_at)->format('d/m/Y H:i');
                                $bg =
                                    $tipo === 'saida'
                                        ? 'linear-gradient(135deg,#ef4444,#fb923c)'
                                        : ($tipo === 'entrada'
                                            ? 'linear-gradient(135deg,#10b981,#06b6d4)'
                                            : '#6b7280');
                                $badgeBg =
                                    $tipo === 'saida' ? '#ffebe9' : ($tipo === 'entrada' ? '#e6ffef' : '#f3f4f6');
                                $badgeColor =
                                    $tipo === 'saida' ? '#b91c1c' : ($tipo === 'entrada' ? '#065f46' : '#374151');
                            @endphp

                            <li class="list-item">
                                <div class="icon-box" style="background: {{ $bg }};">
                                    @if ($quant)
                                        {{ $quant }}
                                    @else
                                        <i class="ti-archive" style="font-size:16px;"></i>
                                    @endif
                                </div>

                                <div class="meta">
                                    <strong title="{{ $itemNome }}">{{ $itemNome }}</strong>
                                    <small>Por {{ $usuarioNome }}</small>

                                    @if (!empty($l->observacao ?? $l->nota))
                                        <div style="margin-top:.35rem;color:var(--muted);font-size:.92rem;">
                                            {{ \Illuminate\Support\Str::limit($l->observacao ?? $l->nota, 140) }}
                                        </div>
                                    @endif
                                </div>

                                <div class="right">
                                    @if ($tipo)
                                        <span
                                            style="background:{{ $badgeBg }};color:{{ $badgeColor }};padding:.25rem .5rem;border-radius:.5rem;font-size:.8rem;">{{ ucfirst($tipo) }}</span>
                                    @endif
                                    <div class="text-muted" style="font-size:.85rem;margin-top:.35rem;">{{ $time }}
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="text-muted">Nenhum lançamento recente.</li>
                        @endforelse
                    </ul>
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

        function createGradient(ctx, x0, y0, x1, y1, colorA, colorB) {
            const g = ctx.createLinearGradient(x0, y0, x1, y1);
            g.addColorStop(0, colorA);
            g.addColorStop(1, colorB || colorA);
            return g;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // animação KPIs
            (function animateCounts(duration = 900) {
                document.querySelectorAll('.kpi-value').forEach(el => {
                    const target = Number(el.getAttribute('data-target') || 0);
                    const startTime = performance.now();

                    function tick(now) {
                        const progress = Math.min((now - startTime) / duration, 1);
                        const value = Math.floor(progress * target);
                        el.textContent = value.toLocaleString();
                        if (progress < 1) requestAnimationFrame(tick);
                    }
                    requestAnimationFrame(tick);
                });
            })();

            const solicitacaoResumo = {!! json_encode((object) ($solicitacaoResumo ?? [])) !!};
            const solicitacaoStatusLabels = {!! json_encode($solicitacaoStatusLabels ?? (object) []) !!};
            const lr = {!! json_encode((object) ($lancamentoResumo ?? [])) !!};

            // Solicitações - doughnut com total central
            (function() {
                const ctx = document.getElementById('chartSolicitacoes');
                if (!ctx) return;
                const labels = Object.keys(solicitacaoResumo).map(key => (solicitacaoStatusLabels[key] &&
                    solicitacaoStatusLabels[key].label) ? solicitacaoStatusLabels[key].label : key);
                const data = Object.values(solicitacaoResumo).map(v => Number(v || 0));
                const total = data.reduce((s, v) => s + v, 0);
                const palette = [
                    cssColorOf('text-warning', '#f59e0b'),
                    cssColorOf('text-info', '#6366f1'),
                    cssColorOf('text-success', '#10b981'),
                    cssColorOf('text-danger', '#ef4444'),
                    cssColorOf('text-primary', '#3b82f6'),
                    cssColorOf('text-secondary', '#6b7280')
                ];

                const centerTextPlugin = {
                    id: 'centerText',
                    afterDraw(chart) {
                        const {
                            ctx,
                            chartArea: {
                                width,
                                height,
                                top,
                                left
                            }
                        } = chart;
                        ctx.save();
                        ctx.fillStyle = '#0b1220';
                        ctx.font =
                            '700 18px Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        const x = left + width / 2;
                        const y = top + height / 2;
                        ctx.fillText(total.toLocaleString(), x, y - 8);
                        ctx.font = '400 12px Inter, system-ui';
                        ctx.fillStyle = '#6b7280';
                        ctx.fillText('Total', x, y + 12);
                        ctx.restore();
                    }
                };

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels,
                        datasets: [{
                            data,
                            backgroundColor: palette.slice(0, data.length),
                            hoverOffset: 8,
                            borderColor: '#fff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        const v = Number(ctx.raw || 0);
                                        const pct = total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';
                                        return `${ctx.label}: ${v.toLocaleString()} (${pct}%)`;
                                    }
                                }
                            }
                        },
                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart'
                        }
                    },
                    plugins: [centerTextPlugin]
                });
            })();

            // Lançamentos - barras com gradiente
            (function() {
                const ctxEl = document.getElementById('chartLancamentos');
                if (!ctxEl) return;
                const entradas = Number(lr.entradas ?? lr.hoje ?? 0);
                const saidas = Number(lr.saidas ?? 0);
                const data = [entradas, saidas];
                const ctx = ctxEl.getContext('2d');
                const gradA = createGradient(ctx, 0, 0, ctxEl.width, 0, '#10b981', '#06b6d4');
                const gradB = createGradient(ctx, 0, 0, ctxEl.width, 0, '#ef4444', '#fb923c');

                new Chart(ctxEl, {
                    type: 'bar',
                    data: {
                        labels: ['Entradas', 'Saídas'],
                        datasets: [{
                            label: 'Movimentos',
                            data,
                            backgroundColor: [gradA, gradB],
                            borderRadius: 8,
                            borderSkipped: false,
                            maxBarThickness: 64
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label(ctx) {
                                        return `${ctx.label}: ${Number(ctx.raw || 0).toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        animation: {
                            duration: 900,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            })();
        });
    </script>
@endsection
