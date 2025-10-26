@extends('layouts.master', ['titulo' => 'Controle de Faltas', 'subtitulo' => 'Registro de faltas dos usuários'])

@section('css')
@endsection

@section('conteudo')
    {{-- Filtros --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Controle de Faltas - {{ date('d/m/Y', strtotime($dataFiltro)) }}</h3>
            </div>
            <form class="form-material" method="GET" role="form">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_filtro" name="data_filtro" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_filtro', date('d/m/Y', strtotime($dataFiltro))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="usuario_id" id="usuario_id" class="form-control select2">
                                <option value="">Todos os Usuários</option>
                                @foreach ($usuariosSelect as $usuario)
                                    <option value="{{ $usuario->id }}" {{ $usuarioId == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="usuario_id" class="float-label">Usuário</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="status_presenca" id="status_presenca" class="form-control">
                                <option value="">Todos os Status</option>
                                <option value="presente" {{ request('status_presenca') == 'presente' ? 'selected' : '' }}>
                                    Presentes
                                </option>
                                <option value="ausente" {{ request('status_presenca') == 'ausente' ? 'selected' : '' }}>
                                    Ausentes
                                </option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="status_presenca" class="float-label">Filtrar por Presença</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Filtrar
                            </button>
                            <a class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.frequencia.index') }}">
                                <i class="ti-close"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Cards de Resumo do Dia --}}
    <div class="col-sm-12">
        <div class="row">

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary text-white rounded">
                                <i class="ti-user" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $totalUsuarios }}</h4>
                                <span class="text-muted">Total Usuários</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success text-white rounded">
                                <i class="ti-check" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $totalPresentesHoje }}</h4>
                                <span class="text-muted">Presentes Hoje</span>
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
                                <h4 class="mb-0">{{ $totalFaltasHoje }}</h4>
                                <span class="text-muted">Faltas Hoje</span>
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
                                <i class="ti-percent" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                @php
                                    $percentualPresenca =
                                        $totalUsuarios > 0 ? round(($totalPresentesHoje / $totalUsuarios) * 100, 1) : 0;
                                @endphp
                                <h4 class="mb-0">{{ $percentualPresenca }}%</h4>
                                <span class="text-muted">Taxa de Presença</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela Principal --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0">
                        Lista de Usuários - {{ \Carbon\Carbon::parse($dataFiltro)->format('d/m/Y') }}
                        @if (request('status_presenca'))
                            @if (request('status_presenca') == 'presente')
                                <span class="badge badge-success ml-2">✅ Apenas Presentes</span>
                            @else
                                <span class="badge badge-danger ml-2">❌ Apenas Ausentes</span>
                            @endif
                        @endif
                        @if (request('usuario_id'))
                            <span class="badge badge-info ml-2">👤 Usuário Filtrado</span>
                        @endif
                    </h5>
                    <small class="text-muted">
                        @if (request('status_presenca'))
                            Exibindo apenas usuários
                            {{ request('status_presenca') == 'presente' ? 'presentes' : 'ausentes' }} do dia.
                        @else
                            Registre apenas as faltas. Quem não está na lista de faltas está automaticamente presente.
                        @endif
                    </small>
                </div>
                <div class="d-flex flex-wrap" style="gap: 5px;">
                    {{-- Dashboard das Faltas --}}
                    @can('acesso', 'administracao.rh.frequencia.relatorio.detalhado')
                        <a href="{{ route('administracao.rh.frequencia.relatorio.detalhado') }}"
                            class="btn btn-sm btn-info btn-out-dashed waves-effect waves-light">
                            <i class="ti-bar-chart"></i> Dashboard das Faltas
                        </a>
                    @endcan
                    {{-- Histórico --}}
                    @can('acesso', 'administracao.rh.frequencia.historico')
                        <a href="{{ route('administracao.rh.frequencia.historico') }}"
                            class="btn btn-sm btn-secondary btn-out-dashed waves-effect waves-light">
                            <i class="ti-time"></i> Histórico Geral
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-left col-md-4">Usuário</th>
                                <th class="text-center col-md-2">Status Hoje</th>
                                <th class="text-center col-md-2">Faltas no Mês</th>
                                <th class="text-center col-md-4">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($frequenciaUsuarios as $freq)
                                <tr>
                                    <td class="text-left col-md-4">
                                        {{ $freq['usuario']->nome }}<br>
                                        <small
                                            class="text-muted">{{ $freq['usuario']->perfil->nome ?? 'Sem perfil' }}</small>
                                    </td>
                                    <td class="text-center col-md-2">
                                        @if ($freq['status_hoje'] == 'presente')
                                            <span class="pcoded-badge label label-success">PRESENTE</span>
                                        @else
                                            <span class="pcoded-badge label label-danger">AUSENTE</span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-2">
                                        @if ($freq['total_faltas_mes'] > 5)
                                            <span
                                                class="pcoded-badge label label-danger">{{ $freq['total_faltas_mes'] }}</span>
                                        @elseif($freq['total_faltas_mes'] > 2)
                                            <span
                                                class="pcoded-badge label label-warning">{{ $freq['total_faltas_mes'] }}</span>
                                        @else
                                            <span
                                                class="pcoded-badge label label-success">{{ $freq['total_faltas_mes'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-4">
                                        <div class="text-center table-actions">
                                            @if ($freq['status_hoje'] == 'presente')
                                                @can('acesso', 'administracao.rh.frequencia.registrar.falta')
                                                    <a type="button" class="btn btn-sm btn-danger btn-marcar-falta"
                                                        data-usuario-id="{{ $freq['usuario']->id }}"
                                                        data-usuario-nome="{{ $freq['usuario']->nome }}"
                                                        title="Registrar falta">
                                                        <i class="ti-close"></i> Marcar Falta
                                                    </a>
                                                @endcan
                                            @else
                                                @can('acesso', 'administracao.rh.frequencia.remover.falta')
                                                    <a type="button" class="btn btn-sm btn-success btn-remover-falta"
                                                        data-usuario-id="{{ $freq['usuario']->id }}"
                                                        data-usuario-nome="{{ $freq['usuario']->nome }}"
                                                        title="Remover falta">
                                                        <i class="ti-check"></i> Remover Falta
                                                    </a>
                                                @endcan
                                            @endif

                                            @can('acesso', 'administracao.rh.frequencia.historico')
                                                <a type="button" class="btn btn-sm btn-info btn-ver-historico"
                                                    data-usuario-id="{{ $freq['usuario']->id }}"
                                                    data-usuario-nome="{{ $freq['usuario']->nome }}" title="Ver histórico"
                                                    href="{{ route('administracao.rh.frequencia.historico', ['usuario_id' => $freq['usuario']->id]) }}">
                                                    <i class="ti-time"></i> Histórico
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        Registros não encontrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="4">
                                Total de Registros: <strong>{{ $frequenciaUsuarios->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Registrar Falta --}}
    <div class="modal fade" id="modalRegistrarFalta" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Falta</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <strong>Atenção:</strong> Você está registrando falta para <strong id="nomeUsuarioFalta"></strong>
                        no dia <strong id="dataFaltaTexto">{{ date('d/m/Y') }}</strong>.
                    </div>
                    <form id="formFalta">
                        <input type="hidden" id="usuarioIdFalta">
                        <input type="hidden" id="dataFalta" value="{{ $dataFiltro }}">
                        <div class="form-group">
                            <label>Motivo da Falta:</label>
                            <textarea id="motivoFalta" class="form-control" rows="3"
                                placeholder="Informe o motivo da falta (obrigatório)..." required minlength="5"></textarea>
                            <small class="text-muted">Mínimo 5 caracteres</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmarFalta">
                        <i class="ti-close"></i> Confirmar Falta
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        /* Estilo para o select de status de presença */
        #status_presenca {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        #status_presenca option {
            padding: 8px 12px;
            font-weight: 500;
        }

        /* Badges no cabeçalho */
        .badge {
            font-size: 0.75rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
        }

        /* Destaque para filtros ativos */
        .card-header .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }

            100% {
                opacity: 1;
            }
        }
    </style>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let usuarioSelecionado = null;

            // Botão Registrar Presença (geral)
            $('#btnRegistrarPresenca').click(function() {
                $('#modalRegistrarPresenca').modal('show');
            });

            // Botão Marcar Falta
            $(document).on('click', '.btn-marcar-falta', function() {
                const usuarioId = $(this).data('usuario-id');
                const usuarioNome = $(this).data('usuario-nome');

                $('#usuarioIdFalta').val(usuarioId);
                $('#nomeUsuarioFalta').text(usuarioNome);
                $('#dataFaltaTexto').text('{{ date('d/m/Y', strtotime($dataFiltro)) }}');
                $('#motivoFalta').val('');
                $('#modalRegistrarFalta').modal('show');
            });

            // Botão Remover Falta
            $(document).on('click', '.btn-remover-falta', function() {
                const usuarioId = $(this).data('usuario-id');
                const usuarioNome = $(this).data('usuario-nome');

                Swal.fire({
                    title: 'Remover Falta',
                    text: `Tem certeza que deseja remover a falta de ${usuarioNome}?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, remover',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        removerFalta(usuarioId);
                    }
                });
            });

            // Confirmar Falta
            $('#confirmarFalta').click(function() {
                if (!$('#formFalta')[0].checkValidity()) {
                    $('#formFalta')[0].reportValidity();
                    return;
                }

                const dados = {
                    usuario_id: $('#usuarioIdFalta').val(),
                    data_entrada: $('#dataFalta').val(),
                    motivo: $('#motivoFalta').val(),
                    _token: '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route('administracao.rh.frequencia.registrar.falta') }}',
                    method: 'POST',
                    data: dados,
                    beforeSend: function() {
                        $('#confirmarFalta').prop('disabled', true).html(
                            '<i class="ti-reload"></i> Registrando...');
                    },
                    success: function(response) {
                        $('#modalRegistrarFalta').modal('hide');
                        Swal.fire('Sucesso!', 'Falta registrada com sucesso!', 'success').then(
                            () => {
                                location.reload();
                            });
                    },
                    error: function(xhr) {
                        let erro = 'Erro interno do servidor';

                        if (xhr.responseJSON) {
                            erro = xhr.responseJSON.message;
                            if (xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                erro = errors.join('<br>');
                            }
                        }

                        Swal.fire({
                            title: 'Erro!',
                            html: 'Erro ao registrar falta:<br>' + erro,
                            icon: 'error'
                        });
                    },
                    complete: function() {
                        $('#confirmarFalta').prop('disabled', false).html(
                            '<i class="ti-close"></i> Confirmar Falta');
                    }
                });
            });

            // Função para remover falta
            function removerFalta(usuarioId) {
                $.ajax({
                    url: '{{ route('administracao.rh.frequencia.remover.falta') }}',
                    method: 'POST',
                    data: {
                        usuario_id: usuarioId,
                        data_entrada: '{{ $dataFiltro }}',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Sucesso!', 'Falta removida com sucesso!', 'success').then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let erro = 'Erro interno do servidor';

                        if (xhr.responseJSON) {
                            erro = xhr.responseJSON.message;
                        }

                        Swal.fire('Erro!', erro, 'error');
                    }
                });
            }

            // Limpar formulário ao fechar modal
            $('#modalRegistrarFalta').on('hidden.bs.modal', function() {
                $('#formFalta')[0].reset();
                $('#usuarioIdFalta').val('');
            });
        });
    </script>
@endsection
