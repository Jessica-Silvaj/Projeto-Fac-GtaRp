@extends('layouts.master', ['titulo' => 'Relatório de Repasses', 'subtitulo' => 'Consulta e análise de repasses realizados'])

@section('conteudo')
    {{-- Filtros --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">Relatório de Repasses</h5>
                        <small class="text-muted">Consulta e análise de repasses realizados</small>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('financeiro.index') }}" class="btn btn-sm btn-secondary btn-out-dashed">
                            <i class="ti-arrow-left"></i> Voltar ao Financeiro
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" method="GET" role="form">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" id="data_inicio" name="data_inicio" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_inicio', request('data_inicio', $dataInicio->format('d/m/Y'))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Início</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" id="data_fim" name="data_fim" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_fim', request('data_fim', $dataFim->format('d/m/Y'))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Fim</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="vendedor_id" id="vendedor_id" class="form-control select2">
                                <option value="">Todos os Vendedores</option>
                                @foreach ($usuariosSelect as $usuario)
                                    <option value="{{ $usuario->id }}"
                                        {{ request('vendedor_id') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="vendedor_id" class="float-label">Vendedor</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="status_repasse" id="status_repasse" class="form-control select2">
                                <option value="">Todos os Status</option>
                                <option value="ativo" {{ request('status_repasse') == 'ativo' ? 'selected' : '' }}>Ativo
                                </option>
                                <option value="desfeito" {{ request('status_repasse') == 'desfeito' ? 'selected' : '' }}>
                                    Desfeito</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="status_repasse" class="float-label">Status do Repasse</label>
                        </div>
                    </div>
                    <div class="row justify-content-center">
                        <div class="col-md-12 text-center">
                            <button type="submit"
                                class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light mr-1">
                                <i class="ti-search"></i> Filtrar
                            </button>
                            <a class="btn btn-secondary btn-sm btn-out-dashed waves-effect waves-light mr-1"
                                href="{{ route('financeiro.relatorio') }}">
                                <i class="ti-reload"></i> Limpar
                            </a>
                            <a class="btn btn-info btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('financeiro.relatorio.exportar', request()->query()) }}">
                                <i class="ti-download"></i> Exportar CSV
                            </a>
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
                            <div class="p-2 bg-primary text-white rounded">
                                <i class="ti-receipt" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ number_format($estatisticas['total_repasses'], 0, ',', '.') }}</h4>
                                <span class="text-muted">Total de Repasses</span>
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
                                <i class="ti-money" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">R$
                                    {{ number_format($estatisticas['valor_total_repassado'], 0, ',', '.') }}</h4>
                                <span class="text-muted">Valor Total</span>
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
                                <i class="ti-bar-chart" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">R$ {{ number_format($estatisticas['media_repasse'], 0, ',', '.') }}</h4>
                                <span class="text-muted">Média por Repasse</span>
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
                                <i class="ti-user" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ $estatisticas['vendedores_ativos'] }}</h4>
                                <span class="text-muted">Vendedores Ativos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela de Repasses --}}
    <div class="col-sm-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">Histórico de Repasses</h5>
                        <small class="text-muted">{{ $repasses->total() }} repasses encontrados no período</small>
                    </div>
                    <div class="col-md-4 text-right">
                        <span class="badge badge-info p-2">
                            <i class="ti-calendar mr-1"></i>
                            {{ request('data_inicio', $dataInicio->format('d/m/Y')) }} à
                            {{ request('data_fim', $dataFim->format('d/m/Y')) }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-block">
                @if ($repasses->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-top-0">Data/Hora</th>
                                    <th class="border-top-0">Vendedor</th>
                                    <th class="border-top-0">Receptor</th>
                                    <th class="border-top-0 text-right">Valor Limpo</th>
                                    <th class="border-top-0 text-right">Valor Sujo</th>
                                    <th class="border-top-0 text-right">Total</th>
                                    <th class="border-top-0 text-center">Status</th>
                                    <th class="border-top-0">Observações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($repasses as $repasse)
                                    <tr class="{{ $repasse->status === 'desfeito' ? 'table-light' : '' }}">
                                        <td>
                                            <div class="d-flex flex-column">
                                                <strong>{{ \Carbon\Carbon::parse($repasse->created_at)->format('d/m/Y') }}</strong>
                                                <small
                                                    class="text-muted">{{ \Carbon\Carbon::parse($repasse->created_at)->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="ti-user bg-primary text-white rounded p-1 mr-2"
                                                    style="font-size: 0.8rem;"></i>
                                                <strong>{{ $repasse->vendedor_nome }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="ti-target bg-secondary text-white rounded p-1 mr-2"
                                                    style="font-size: 0.8rem;"></i>
                                                <strong>{{ $repasse->receptor_nome }}</strong>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-success">
                                                R$ {{ number_format($repasse->valor_limpo, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="badge badge-warning">
                                                R$ {{ number_format($repasse->valor_sujo, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <strong class="h6 mb-0">R$
                                                {{ number_format($repasse->valor_total, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if ($repasse->status === 'ativo')
                                                <span class="badge badge-success">
                                                    <i class="ti-check mr-1"></i>Ativo
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="ti-close mr-1"></i>Desfeito
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($repasse->observacoes)
                                                <small class="text-muted">{{ $repasse->observacoes }}</small>
                                            @else
                                                <small class="text-muted font-italic">Sem observações</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginação --}}
                    @if ($repasses->hasPages())
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Mostrando {{ $repasses->firstItem() }} a {{ $repasses->lastItem() }} de
                                    {{ $repasses->total() }} resultados
                                </small>
                                {{ $repasses->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="ti-info-alt text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3 text-muted">Nenhum repasse encontrado</h5>
                        <p class="text-muted">Não há repasses que correspondam aos filtros selecionados.</p>
                        <a href="{{ route('financeiro.relatorio') }}" class="btn btn-primary btn-sm">
                            <i class="ti-reload mr-1"></i>Limpar Filtros
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('script')
@endsection
