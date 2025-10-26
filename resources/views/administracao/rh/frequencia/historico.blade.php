@extends('layouts.master', ['titulo' => 'Histórico de Frequência', 'subtitulo' => 'Histórico detalhado de presenças e faltas'])

@section('css')
@endsection

@section('conteudo')
    {{-- Filtros --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" method="GET" role="form">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" id="data_inicio" name="data_inicio" class="form-control"
                                value="{{ $dataInicio }}">
                            <span class="form-bar"></span>
                            <label for="data_inicio" class="float-label">Data Início</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" id="data_fim" name="data_fim" class="form-control"
                                value="{{ $dataFim }}">
                            <span class="form-bar"></span>
                            <label for="data_fim" class="float-label">Data Fim</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="usuario_id" id="usuario_id" class="form-control">
                                <option value="" {{ empty($usuarioId) ? 'selected' : '' }}>-- Todos os Usuários --
                                </option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ $usuarioId == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="usuario_id" class="float-label">Usuário</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Pesquisar
                            </button>
                            <a class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.frequencia.historico') }}">
                                <i class="ti-close"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Histórico --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h3>Histórico de Registros - {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a
                            {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        @can('acesso', 'administracao.rh.frequencia.index')
                            <a href="{{ route('administracao.rh.frequencia.index') }}"
                                class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-back-left"></i> Voltar ao Controle
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-left col-md-2">Data</th>
                                <th class="text-left col-md-2">Usuário</th>
                                <th class="text-center col-md-1">Status</th>
                                <th class="text-left col-md-4">Motivo da Falta</th>
                                <th class="text-center col-md-2">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historico as $falta)
                                <tr>
                                    <td class="text-left col-md-2">
                                        @php
                                            $dataFalta = $falta->data_falta
                                                ? date('d/m/Y', strtotime($falta->data_falta))
                                                : '';
                                            $diaSemana = $falta->data_falta
                                                ? \Carbon\Carbon::parse($falta->data_falta)->locale('pt_BR')->dayName
                                                : '';
                                        @endphp
                                        <strong>{{ $dataFalta }}</strong>
                                        @if ($diaSemana)
                                            <br><small class="text-muted">{{ ucfirst($diaSemana) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-left col-md-2">
                                        <strong>{{ $falta->usuario->nome }}</strong><br>
                                        <small
                                            class="text-muted">{{ $falta->usuario->perfil->nome ?? 'Sem perfil' }}</small>
                                    </td>
                                    <td class="text-center col-md-1">
                                        <span class="pcoded-badge label label-danger">
                                            <i class="ti-close"></i> FALTA
                                        </span>
                                    </td>
                                    <td class="text-left col-md-4">
                                        {{ $falta->motivo ?? 'Sem motivo informado' }}
                                    </td>
                                    <td class="text-center col-md-2">
                                        <small class="text-muted">
                                            <i class="ti-user"></i>
                                            {{ $falta->registrado_por ?? auth()->user()->nome }}
                                        </small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="text-center p-4">
                                            <i class="ti-info-alt text-muted" style="font-size: 2rem;"></i>
                                            <h5 class="text-muted mt-2">Nenhum registro encontrado</h5>
                                            <p class="text-muted">Não há faltas registradas no período selecionado</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="6">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="ti-clipboard"></i>
                                        Total de Registros:
                                        <strong>{{ method_exists($historico, 'total') ? $historico->total() : $historico->count() }}</strong>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="ti-info-alt"></i>
                                        Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a
                                        {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
                                    </span>
                                </div>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $historico->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection

@section('css')
    <style>
        /* Melhoria visual da tabela */
        .table td {
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            console.log('Histórico de frequência carregado!');
            console.log('Botões encontrados:', $('.btn-remover-falta-historico').length);

            // Tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Animação sutil ao carregar
            $('.table tbody tr').each(function(index) {
                $(this).css('opacity', '0').delay(index * 50).animate({
                    opacity: 1
                }, 300);
            });
        });
    </script>
@endsection
