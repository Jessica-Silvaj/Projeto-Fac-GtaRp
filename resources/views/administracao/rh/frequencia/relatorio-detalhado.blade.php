@extends('layouts.master', ['titulo' => 'Relatório Detalhado', 'subtitulo' => 'Relatório completo de frequência e faltas'])

@section('conteudo')
    {{-- Filtros --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>Filtros</h5>
            </div>
            <form class="form-material" method="GET" role="form">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_inicio" name="data_inicio" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_inicio', date('d/m/Y', strtotime($dataInicio))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Início</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_fim" name="data_fim" class="form-control data-mask"
                                data-format="DD/MM/YYYY" value="{{ old('data_fim', date('d/m/Y', strtotime($dataFim))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Fim</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="usuario_id" id="usuario_id" class="form-control">
                                <option value="">Todos os Usuários</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ $usuarioId == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Usuário</label>
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
                                href="{{ route('administracao.rh.frequencia.relatorio.detalhado') }}">
                                <i class="ti-close"></i> Limpar
                            </a>
                            @can('acesso', 'administracao.rh.frequencia.index')
                                <a class="btn btn-info btn-sm btn-out-dashed waves-effect waves-light"
                                    href="{{ route('administracao.rh.frequencia.index') }}">
                                    <i class="ti-arrow-left"></i> Voltar
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Estatísticas --}}
    <div class="col-sm-12">
        <div class="row">
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

            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning text-white rounded">
                                <i class="ti-user" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $usuariosComFaltas }}</h4>
                                <span class="text-muted">Usuários com Faltas</span>
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
                                <i class="ti-bar-chart" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $mediaFaltasPorUsuario }}</h4>
                                <span class="text-muted">Média por Usuário</span>
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
                                <i class="ti-calendar" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                @php
                                    $dias =
                                        \Carbon\Carbon::parse($dataInicio)->diffInDays(
                                            \Carbon\Carbon::parse($dataFim),
                                        ) + 1;
                                @endphp
                                <h4 class="mb-0">{{ $dias }}</h4>
                                <span class="text-muted">Dias Analisados</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela de Histórico --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5>Histórico Detalhado - Período: {{ date('d/m/Y', strtotime($dataInicio)) }} à
                    {{ date('d/m/Y', strtotime($dataFim)) }}</h5>
                @if ($usuarioId)
                    <small class="text-muted">Filtrado por usuário específico</small>
                @endif
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-left col-md-3">Usuário</th>
                                <th class="text-center col-md-2">Data da Falta</th>
                                <th class="text-left col-md-4">Motivo</th>
                                <th class="text-center col-md-2">Registrado em</th>
                                <th class="text-center col-md-1">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($historico as $falta)
                                <tr>
                                    <td class="text-left col-md-3">
                                        <strong>{{ $falta->usuario->nome }}</strong><br>
                                        <small
                                            class="text-muted">{{ $falta->usuario->perfil->nome ?? 'Sem perfil' }}</small>
                                    </td>
                                    <td class="text-center col-md-2">
                                        {{ date('d/m/Y', strtotime($falta->data_falta)) }}
                                    </td>
                                    <td class="text-left col-md-4">
                                        {{ $falta->motivo ?? 'Sem motivo especificado' }}
                                    </td>
                                    <td class="text-center col-md-2">
                                        {{ auth()->user()->nome }}
                                    </td>
                                    <td class="text-center col-md-1">
                                        <span class="pcoded-badge label label-danger">FALTA</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        @if ($usuarioId)
                                            Nenhuma falta encontrada para o usuário selecionado no período informado
                                        @else
                                            Nenhuma falta registrada no período informado
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="5" class="bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">
                                        <i class="ti-clipboard"></i>
                                        Total de Registros:
                                        <strong>{{ method_exists($historico, 'total') ? $historico->total() : $historico->count() }}</strong>
                                    </span>
                                    <span class="text-muted small">
                                        <i class="ti-calendar"></i>
                                        Relatório detalhado de frequência
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

@section('script')
    <script>
        $(document).ready(function() {
            // Página carregada
        });
    </script>
@endsection
