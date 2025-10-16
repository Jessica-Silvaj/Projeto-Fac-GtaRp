@extends('layouts.master', ['titulo' => 'Solicitações do Discord', 'subtitulo' => 'Pendências de Entrada / Saída'])

@php
    use App\Models\DiscordSolicitacao;

    $statusLabels = [
        DiscordSolicitacao::STATUS_PENDENTE => ['label' => 'Pendente', 'class' => 'badge-warning'],
        DiscordSolicitacao::STATUS_AJUSTE => ['label' => 'Em ajuste', 'class' => 'badge-info'],
        DiscordSolicitacao::STATUS_APROVADA => ['label' => 'Aprovada', 'class' => 'badge-success'],
        DiscordSolicitacao::STATUS_REJEITADA => ['label' => 'Rejeitada', 'class' => 'badge-danger'],
    ];
@endphp

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" method="GET" action="{{ route('bau.lancamentos.solicitacoes.index') }}">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="status" class="form-control select2">
                                <option value="todos" {{ $statusSelecionado === 'todos' ? 'selected' : '' }}>Todos</option>
                                @foreach ($statusLabels as $valor => $info)
                                    <option value="{{ $valor }}"
                                        {{ $statusSelecionado === $valor ? 'selected' : '' }}>{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Status</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="tipo" class="form-control select2">
                                <option value="">Todos</option>
                                <option value="ENTRADA" {{ $tipoSelecionado === 'ENTRADA' ? 'selected' : '' }}>Entrada</option>
                                <option value="SAIDA" {{ $tipoSelecionado === 'SAIDA' ? 'selected' : '' }}>Saída</option>
                                <option value="TRANSFERENCIA" {{ $tipoSelecionado === 'TRANSFERENCIA' ? 'selected' : '' }}>
                                    Transferência</option>
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Tipo</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" name="busca" class="form-control"
                                value="{{ request('busca') }}" placeholder="ID, usuário, observação...">
                            <span class="form-bar"></span>
                            <label class="float-label">Busca rápida</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                        <i class="ti-search"></i> Filtrar
                    </button>
                    <a href="{{ route('bau.lancamentos.solicitacoes.index') }}"
                        class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light">
                        <i class="ti-close"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            @foreach ($statusLabels as $valor => $info)
                <div class="col-md-3 col-sm-6">
                    <div class="card">
                        <div class="card-block text-center">
                            <h4 class="mb-1">{{ $info['label'] }}</h4>
                            <span class="display-4">
                                {{ $statusResumo[$valor] ?? 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Solicitações Recebidas</h3>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Itens</th>
                                <th>Recebido em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($solicitacoes as $solicitacao)
                                <tr>
                                    <td>{{ $solicitacao->id }}</td>
                                    <td>
                                        <span class="badge badge-inverse-primary">
                                            {{ $solicitacao->tipo }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $info = $statusLabels[$solicitacao->status] ?? null;
                                        @endphp
                                        @if ($info)
                                            <span class="badge {{ $info['class'] }}">{{ $info['label'] }}</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($solicitacao->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $itens = collect($solicitacao->itens ?? []);
                                        @endphp
                                        @if ($itens->isEmpty())
                                            <span class="text-muted">Sem itens estruturados</span>
                                        @else
                                            <ul class="m-0 pl-3">
                                                @foreach ($itens as $item)
                                                    <li>
                                                        {{ data_get($item, 'quantidade', '?') }}x
                                                        {{ data_get($item, 'nome') ?? data_get($item, 'descricao', 'Item') }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                    <td>{{ optional($solicitacao->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('bau.lancamentos.solicitacoes.edit', $solicitacao) }}"
                                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                                            <i class="ti-pencil"></i> Ajustar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Nenhuma solicitação encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">
                    {{ $solicitacoes->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
