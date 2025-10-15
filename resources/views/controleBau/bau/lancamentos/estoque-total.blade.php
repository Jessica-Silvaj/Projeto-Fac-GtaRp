@extends('layouts.master', ['titulo' => 'Estoque Total', 'subtitulo' => 'Saldos consolidados por item e baú'])

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form id="estoque-form" class="form-material" action="{{ route('bau.lancamentos.estoque') }}" method="GET">
                <div class="card-block">
                    <div class="form-row align-items-end">
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" id="inicio" name="inicio" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ !empty($inicio) ? \Carbon\Carbon::parse($inicio)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label for="inicio" class="float-label">Desde (opcional)</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3 mb-3">
                            <input type="text" id="fim" name="fim" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ !empty($fim) ? \Carbon\Carbon::parse($fim)->format('d/m/Y') : '' }}">
                            <span class="form-bar"></span>
                            <label for="fim" class="float-label">Até (opcional)</label>
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
                            <select name="bau_id" id="bau_id" class="form-control select2"
                                data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}"
                                placeholder="Filtrar por baú">
                                <option value=""></option>
                                @if (!empty($bauSelecionado))
                                    <option value="{{ $bauSelecionado['id'] }}" selected>{{ $bauSelecionado['nome'] }}
                                    </option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label for="bau_id" class="float-label">Baú (opcional)</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Atualizar
                            </button>
                            <button type="button" id="btn-limpar-estoque"
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
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Total em Estoque</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-0">{{ number_format($totais['quantidade_total'] ?? 0, 0, ',', '.') }}</h2>
                        <small class="text-muted">Soma de saldos positivos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Itens Únicos</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-0">{{ number_format($totais['itens_unicos'] ?? 0, 0, ',', '.') }}</h2>
                        <small class="text-muted">Itens com saldo diferente de zero</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex align-items-center">
                        <h3 class="mb-0">Baús Utilizados</h3>
                    </div>
                    <div class="card-body">
                        <h2 class="mb-0">{{ number_format($totais['baus_utilizados'] ?? 0, 0, ',', '.') }}</h2>
                        <small class="text-muted">Baús com saldo disponível</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="mb-0">Ações rápidas</h3>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                    @can('acesso', 'bau.lancamentos.estoque.csv')
                        <a class="btn btn-sm btn-outline-secondary"
                            href="{{ route('bau.lancamentos.estoque.csv', array_merge(request()->all(), ['dataset' => 'detalhes'])) }}">
                            Exportar detalhes CSV
                        </a>
                        <a class="btn btn-sm btn-outline-secondary"
                            href="{{ route('bau.lancamentos.estoque.csv', array_merge(request()->all(), ['dataset' => 'resumo_baus'])) }}">
                            Exportar resumo por baú
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="mb-0">Saldos por Item e Baú</h3>
                <small class="text-muted">Histórico consolidado conforme filtros</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Baú</th>
                                <th class="text-right">Quantidade total</th>
                                <th class="text-right">Itens</th>
                                <th>Itens armazenados</th>
                                <th class="text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($resumoBaus as $bau)
                                <tr>
                                    <td>{{ $bau['bau_nome'] }}</td>
                                    <td class="text-right">{{ number_format($bau['quantidade'], 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($bau['itens'], 0, ',', '.') }}</td>
                                    <td>
                                        <div class="d-flex flex-wrap align-items-center" style="gap: 0.35rem;">
                                            @foreach ($bau['itens_lista'] ?? [] as $item)
                                                <span class="badge badge-info">
                                                    {{ $item['item_nome'] }}:
                                                    {{ number_format($item['quantidade'], 0, ',', '.') }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        @can('acesso', 'bau.lancamentos.historico')
                                            <a class="btn btn-sm btn-primary"
                                                href="{{ route('bau.lancamentos.historico') .
                                                    '?' .
                                                    http_build_query([
                                                        'bau_destino_id' => $bau['bau_id'] ?? null,
                                                        'bau_origem_id' => $bau['bau_id'] ?? null,
                                                        'bau_id' => $bau['bau_id'] ?? null,
                                                    ]) }}">
                                                Ver histórico
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Nenhum saldo encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $resumoBaus->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            var clearBtn = document.getElementById('btn-limpar-estoque');
            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    var form = document.getElementById('estoque-form');
                    if (!form) return;
                    var inicio = document.getElementById('inicio');
                    var fim = document.getElementById('fim');
                    var item = document.getElementById('itens_id');
                    var bau = document.getElementById('bau_id');
                    if (inicio) inicio.value = '';
                    if (fim) fim.value = '';
                    if (item) {
                        item.value = '';
                        try {
                            item.innerHTML = '<option value=""></option>';
                        } catch (e) {}
                    }
                    if (bau) {
                        bau.value = '';
                        try {
                            bau.innerHTML = '<option value=""></option>';
                        } catch (e) {}
                    }
                    form.submit();
                });
            }
        })();
    </script>
@endsection
