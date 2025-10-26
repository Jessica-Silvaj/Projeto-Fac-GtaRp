@extends('layouts.master', ['titulo' => 'Relatório Detalhado de Frequência', 'subtitulo' => 'Análise completa de presenças e faltas'])

@section('conteudo')
    {{-- Header --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3>Relatório Detalhado - {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a
                            {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('administracao.rh.frequencia.index') }}"
                            class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light">
                            <i class="ti-back-left"></i> Voltar ao Controle
                        </a>
                        <button class="btn btn-info btn-sm btn-out-dashed waves-effect waves-light" onclick="window.print()">
                            <i class="ti-printer"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumo Geral --}}
    <div class="col-sm-12">
        <div class="row">
            @php
                $totalUsuarios = count($relatorioDetalhado);
                $usuariosComFalta = collect($relatorioDetalhado)->where('frequencia.dias_ausente', '>', 0)->count();
                $mediaFrequencia = collect($relatorioDetalhado)->avg('frequencia.percentual_frequencia');
                $totalFaltas = collect($relatorioDetalhado)->sum('frequencia.dias_ausente');
            @endphp

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary text-white rounded">
                                <i class="ti-user" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $totalUsuarios }}</h4>
                                <span class="text-muted">Usuários Analisados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning text-white rounded">
                                <i class="ti-alert" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $usuariosComFalta }}</h4>
                                <span class="text-muted">Com Faltas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info text-white rounded">
                                <i class="ti-stats-up" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ number_format($mediaFrequencia, 1) }}%</h4>
                                <span class="text-muted">Média de Frequência</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-danger text-white rounded">
                                <i class="ti-close" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $totalFaltas }}</h4>
                                <span class="text-muted">Total de Faltas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Relatório por Usuário --}}
    @foreach ($relatorioDetalhado as $item)
        <div class="col-sm-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">
                                {{ $item['usuario']->nome }}
                                <small class="text-muted">- {{ $item['usuario']->perfil->nome ?? 'Sem perfil' }}</small>
                            </h5>
                        </div>
                        <div class="col-md-6 text-right">
                            @if ($item['frequencia']['percentual_frequencia'] >= 80)
                                <span class="pcoded-badge label label-success">Frequência Excelente</span>
                            @elseif($item['frequencia']['percentual_frequencia'] >= 60)
                                <span class="pcoded-badge label label-info">Frequência Boa</span>
                            @elseif($item['frequencia']['percentual_frequencia'] >= 40)
                                <span class="pcoded-badge label label-warning">Frequência Regular</span>
                            @else
                                <span class="pcoded-badge label label-danger">Frequência Baixa</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- Estatísticas --}}
                        <div class="col-md-4">
                            <h6>Estatísticas do Período:</h6>
                            <ul class="list-unstyled">
                                <li><strong>Dias no Período:</strong> {{ $item['frequencia']['total_dias'] }}</li>
                                <li><strong>Dias Presente:</strong>
                                    <span
                                        class="pcoded-badge label label-success">{{ $item['frequencia']['dias_presente'] }}</span>
                                </li>
                                <li><strong>Dias Ausente:</strong>
                                    <span
                                        class="pcoded-badge label label-danger">{{ $item['frequencia']['dias_ausente'] }}</span>
                                </li>
                                <li><strong>Sem Registro:</strong>
                                    <span
                                        class="pcoded-badge label label-warning">{{ $item['frequencia']['dias_sem_registro'] }}</span>
                                </li>
                                <li><strong>% Frequência:</strong>
                                    <span
                                        class="pcoded-badge label label-info">{{ $item['frequencia']['percentual_frequencia'] }}%</span>
                                </li>
                            </ul>
                        </div>

                        {{-- Tempo na Cidade --}}
                        <div class="col-md-4">
                            <h6>Tempo na Cidade:</h6>
                            <ul class="list-unstyled">
                                @if ($item['total_horas'])
                                    <li><strong>Total de Horas:</strong> {{ $item['total_horas']['horas'] }}h
                                        {{ $item['total_horas']['minutos'] }}min</li>
                                    @if ($item['media_horas_dia'])
                                        <li><strong>Média por Dia:</strong> {{ $item['media_horas_dia']['horas'] }}h
                                            {{ $item['media_horas_dia']['minutos'] }}min</li>
                                    @endif
                                @else
                                    <li class="text-muted">Sem registros de horário completos</li>
                                @endif
                            </ul>
                        </div>

                        {{-- Ações Recomendadas --}}
                        <div class="col-md-4">
                            <h6>Situação:</h6>
                            @if ($item['frequencia']['percentual_frequencia'] >= 80)
                                <div class="alert alert-success p-2">
                                    <small><strong>Excelente!</strong> Usuário tem boa frequência na cidade.</small>
                                </div>
                            @elseif($item['frequencia']['percentual_frequencia'] >= 60)
                                <div class="alert alert-info p-2">
                                    <small><strong>Boa frequência.</strong> Manter acompanhamento.</small>
                                </div>
                            @elseif($item['frequencia']['percentual_frequencia'] >= 40)
                                <div class="alert alert-warning p-2">
                                    <small><strong>Atenção!</strong> Frequência irregular. Conversar com o usuário.</small>
                                </div>
                            @else
                                <div class="alert alert-danger p-2">
                                    <small><strong>Crítico!</strong> Frequência muito baixa. Ação imediata
                                        necessária.</small>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Detalhes dos Registros --}}
                    @if ($item['registros']->count() > 0)
                        <hr>
                        <h6>Registros do Período:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Entrada</th>
                                        <th>Saída</th>
                                        <th>Status</th>
                                        <th>Tempo</th>
                                        <th>Observações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($item['registros'] as $registro)
                                        <tr>
                                            <td>{{ $registro->data_entrada->format('d/m/Y') }}</td>
                                            <td>
                                                @if ($registro->horario_entrada)
                                                    {{ \Carbon\Carbon::parse($registro->horario_entrada)->format('H:i') }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>
                                                @if ($registro->horario_saida)
                                                    {{ \Carbon\Carbon::parse($registro->horario_saida)->format('H:i') }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>
                                                @php $statusLabel = $registro->status_label; @endphp
                                                <span class="pcoded-badge label label-{{ $statusLabel['classe'] }}">
                                                    {{ $statusLabel['texto'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($registro->tempo_formatado !== 'N/A')
                                                    {{ $registro->tempo_formatado }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td>
                                                @if ($registro->observacoes)
                                                    <small>{{ \Illuminate\Support\Str::limit($registro->observacoes, 50) }}</small>
                                                @else
                                                    --
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <small>Nenhum registro encontrado para este usuário no período selecionado.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    @if (count($relatorioDetalhado) === 0)
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-muted">Nenhum dado encontrado para gerar o relatório</h5>
                    <p class="text-muted">Verifique se existem usuários ativos no sistema.</p>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <style>
        @media print {

            .btn,
            .card-header .btn,
            .no-print {
                display: none !important;
            }

            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
                page-break-inside: avoid;
                margin-bottom: 20px !important;
            }

            .card-header {
                background-color: #f8f9fa !important;
                border-bottom: 1px solid #ddd !important;
            }

            body {
                font-size: 12px !important;
            }

            h3,
            h4,
            h5,
            h6 {
                font-size: 14px !important;
            }

            .pcoded-badge {
                border: 1px solid #ddd !important;
                padding: 2px 6px !important;
                font-size: 10px !important;
            }
        }
    </style>
@endsection
