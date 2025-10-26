@extends('layouts.master', ['titulo' => 'Fila de Vendas', 'subtitulo' => 'Pedidos aguardando atendimento'])

@php
    use App\Models\FilaEspera;
    /**
     * Variaveis esperadas:
     * - $statusLabels: [status => ['label' => string, 'class' => string]]
     * - $statusResumo: [status => quantidade]
     * - $responsaveis: collection/array com responsaveis (id, nome)
     * - $fila: paginator/collection com campos:
     *      organizacao_nome, solicitante_nome, data_pedido (Carbon),
     *      data_entrega_estimada (Carbon|null), responsavel_nome,
     *      status, pedido
     */
@endphp

@section('conteudo')
    @php
        $statusSelecionado = request()->get('status', 'todos');
        $responsavelSelecionado = request()->get('responsavel');
        $statusEstilos = [
            FilaEspera::STATUS_PENDENTE => ['bg' => 'bg-warning', 'label' => 'label-warning', 'icon' => 'ti-timer'],
            FilaEspera::STATUS_EM_ATENDIMENTO => [
                'bg' => 'bg-info',
                'label' => 'label-info',
                'icon' => 'ti-headphone-alt',
            ],
            FilaEspera::STATUS_CONCLUIDO => [
                'bg' => 'bg-success',
                'label' => 'label-success',
                'icon' => 'ti-check-box',
            ],
            FilaEspera::STATUS_CANCELADO => ['bg' => 'bg-danger', 'label' => 'label-danger', 'icon' => 'ti-na'],
        ];
    @endphp
    <div class="col-sm-12 mt-3">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" method="GET" action="{{ route('venda.fila.index') }}">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" id="organizacao" name="organizacao"
                                value="{{ request()->get('organizacao') }}" class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Organizacao</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" id="pessoa" name="pessoa" value="{{ request()->get('pessoa') }}"
                                class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Pessoa</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="status" id="status" class="form-control select2">
                                <option value="todos" {{ $statusSelecionado === 'todos' ? 'selected' : '' }}>Todos os
                                    status</option>
                                @foreach ($statusLabels ?? [] as $valor => $info)
                                    <option value="{{ $valor }}"
                                        {{ $statusSelecionado === (string) $valor ? 'selected' : '' }}>{{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Status</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="responsavel" id="responsavel" class="form-control select2">
                                <option value="" {{ empty($responsavelSelecionado) ? 'selected' : '' }}>Qualquer
                                    responsavel</option>
                                @foreach ($responsaveis ?? [] as $responsavel)
                                    @php
                                        $id = $responsavel->id ?? ($responsavel['id'] ?? $responsavel);
                                        $nome = $responsavel->nome ?? ($responsavel['nome'] ?? $responsavel);
                                    @endphp
                                    <option value="{{ $id }}"
                                        {{ (string) $responsavelSelecionado === (string) $id ? 'selected' : '' }}>
                                        {{ $nome }}</option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Responsavel</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" name="data_pedido_de" value="{{ request()->get('data_pedido_de') }}"
                                class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Pedido a partir de</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" name="data_pedido_ate" value="{{ request()->get('data_pedido_ate') }}"
                                class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Pedido ate</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" name="data_entrega_de" value="{{ request()->get('data_entrega_de') }}"
                                class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Entrega estimada de</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="date" name="data_entrega_ate" value="{{ request()->get('data_entrega_ate') }}"
                                class="form-control">
                            <span class="form-bar"></span>
                            <label class="float-label">Entrega estimada ate</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                        <i class="ti-search"></i> Filtrar
                    </button>
                    <a href="{{ route('venda.fila.index') }}"
                        class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light">
                        <i class="ti-close"></i> Limpar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="row">
            @forelse ($statusLabels ?? [] as $valor => $info)
                @php
                    $estilo = $statusEstilos[$valor] ?? [
                        'bg' => 'bg-secondary',
                        'label' => 'label-default',
                        'icon' => 'ti-layers',
                    ];
                    $quantidadeStatus = (int) ($statusResumo[$valor] ?? 0);
                @endphp
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-block p-0">
                            <div
                                class="px-3 py-2 d-flex justify-content-between align-items-center {{ $estilo['bg'] }} text-white rounded-top">
                                <span class="text-uppercase small">{{ $info['label'] }}</span>
                                <i class="{{ $estilo['icon'] }} font-weight-semibold" style="font-size: 1.25rem;"></i>
                            </div>
                            <div class="p-3 text-center">
                                <span class="display-4 d-block mb-2 text-dark">{{ $quantidadeStatus }}</span>
                                <span class="pcoded-badge label {{ $estilo['label'] }}">Pedidos</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-warning mb-4">
                        Nenhum status configurado para a fila de vendas.
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-sm-12 mt-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="mb-0 mr-2">Pedidos em Fila</h3>
                <div class="d-flex flex-wrap" style="gap: 0.5rem;">
                    @can('acesso', 'venda.fila.historico')
                        <a href="{{ route('venda.fila.historico') }}"
                            class="btn btn-sm btn-info btn-out-dashed waves-effect waves-light">
                            <i class="ti-bar-chart"></i> Histórico de vendas
                        </a>
                    @endcan
                    @can('acesso', 'venda.fila.index')
                        <a href="{{ route('venda.fila.create') }}"
                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                            <i class="ti-plus"></i> Novo
                        </a>
                    @endcan
                </div>
            </div>
            <div class="card-block">
                @php
                    // Verificar se o usuário tem permissões para ações
                    $acoesPermissions = ['venda.fila.edit', 'venda.fila.vender', 'venda.fila.destroy'];

                    $canPerformActions = false;
                    $user = auth()->user();
                    if ($user) {
                        foreach ($acoesPermissions as $perm) {
                            if ($user->can('acesso', $perm)) {
                                $canPerformActions = true;
                                break;
                            }
                        }
                    }
                @endphp

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="col-md-2 text-center">Organizacao / Pessoa</th>
                                <th class="col-md-1">Data do Pedido</th>
                                <th class="col-md-1">Entrega Estimada</th>
                                <th class="col-md-1 text-center">Dias em atraso</th>
                                <th class="col-md-2 text-center">Responsavel</th>
                                <th class="col-md-1 text-center">Status</th>
                                <th class="col-md-3 text-center">Pedido</th>
                                @if ($canPerformActions)
                                    <th class="col-md-3 text-center">Ações</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($fila ?? [] as $registro)
                                @php
                                    $statusInfo = ($statusLabels ?? [])[$registro->status ?? ''] ?? null;
                                    $diasAtraso = null;
                                    if (
                                        in_array(
                                            $registro->status ?? null,
                                            [FilaEspera::STATUS_EM_ATENDIMENTO, FilaEspera::STATUS_PENDENTE],
                                            true,
                                        ) &&
                                        !empty($registro->data_entrega_estimada) &&
                                        $registro->data_entrega_estimada instanceof \Carbon\Carbon &&
                                        $registro->data_entrega_estimada->isPast()
                                    ) {
                                        $diasAtraso = $registro->data_entrega_estimada->diffInDays(now());
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ optional($registro->organizacao)->nome ?? ($registro->organizacao_nome ?? 'Organizacao nao informada') }}</strong><br>
                                        <small
                                            class="text-muted">{{ $registro->nome ?? ($registro->solicitante_nome ?? '---') }}</small>
                                    </td>
                                    <td>{{ optional($registro->data_pedido)->format('d/m/Y') }}</td>
                                    <td>
                                        @if (!empty($registro->data_entrega_estimada))
                                            {{ optional($registro->data_entrega_estimada)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Sem previsao</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($diasAtraso)
                                            <span class="pcoded-badge label label-danger">{{ $diasAtraso }}
                                                dia{{ $diasAtraso > 1 ? 's' : '' }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($registro->usuario)->nome ?? ($registro->responsavel_nome ?? ($registro->responsavel_recebimento ?? '---')) }}
                                    </td>
                                    <td>
                                        @php
                                            $classeStatus = 'pcoded-badge label label-default';
                                            if ($statusInfo && !empty($statusInfo['class'])) {
                                                $classeOriginal = trim($statusInfo['class']);
                                                $classeFormatada = str_replace(
                                                    ['badge ', 'badge-'],
                                                    ['label ', 'label-'],
                                                    $classeOriginal,
                                                );
                                                $classeStatus = 'pcoded-badge label ' . $classeFormatada;
                                            }
                                        @endphp
                                        <span
                                            class="{{ $classeStatus }}">{{ $statusInfo['label'] ?? ucfirst($registro->status ?? 'Indefinido') }}</span>
                                    </td>
                                    <td>
                                        <div class="pedido-resumo">
                                            @if (!empty($registro->itens) && count($registro->itens))
                                                <div class="pedido-resumo__secao">
                                                    <div class="pedido-resumo__titulo">Itens</div>
                                                    <ul class="pedido-resumo__lista">
                                                        @foreach ($registro->itens as $item)
                                                            <li>
                                                                <span
                                                                    class="pedido-resumo__produto">{{ $item->quantidade }}x
                                                                    {{ $item->produto->nome ?? 'Produto #' . $item->produto_id }}</span>
                                                                @if (!empty($item->observacao))
                                                                    <small
                                                                        class="text-muted d-block">{{ $item->observacao }}</small>
                                                                @endif
                                                                <small class="text-muted d-block">
                                                                    Tabela: {{ ucfirst($item->tabela_preco ?? 'padrao') }}
                                                                    |
                                                                    Limpo:
                                                                    {{ number_format($item->preco_unitario_limpo ?? 0, 2, ',', '.') }}
                                                                    |
                                                                    Sujo:
                                                                    {{ number_format($item->preco_unitario_sujo ?? 0, 2, ',', '.') }}
                                                                </small>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif

                                            <div class="pedido-resumo__secao">
                                                <div class="pedido-resumo__titulo">Pedido</div>
                                                <p class="pedido-resumo__texto">
                                                    {{ \Illuminate\Support\Str::limit($registro->pedido, 240) }}</p>
                                            </div>

                                            @php
                                                $valorLimpo = (float) ($registro->dinheiro_limpo ?? 0);
                                                $valorSujo = (float) ($registro->dinheiro_sujo ?? 0);
                                                $tipoPagamento = strtolower($registro->pagamento_tipo ?? '');
                                                $emAtendimento =
                                                    $registro->status === FilaEspera::STATUS_EM_ATENDIMENTO;
                                                $temPagamento = $valorLimpo > 0 || $valorSujo > 0;
                                                $temDesconto =
                                                    $registro->desconto_aplicado && $registro->desconto_valor > 0;

                                                // Determinar quais valores mostrar baseado no tipo de pagamento
                                                $mostrarLimpo =
                                                    $tipoPagamento === 'limpo' || $tipoPagamento === 'ambos';
                                                $mostrarSujo = $tipoPagamento === 'sujo' || $tipoPagamento === 'ambos';

                                                // Se o tipo de pagamento não está definido, mostrar apenas valores > 0
                                                if (empty($tipoPagamento) || $tipoPagamento === 'n/d') {
                                                    $mostrarLimpo = $valorLimpo > 0;
                                                    $mostrarSujo = $valorSujo > 0;
                                                }
                                            @endphp
                                            @if ($temPagamento || $emAtendimento)
                                                <div class="pedido-resumo__secao">
                                                    <div class="pedido-resumo__titulo">Pagamento</div>
                                                    @if ($temPagamento)
                                                        <ul class="pedido-resumo__lista pedido-resumo__lista--pagamento">
                                                            @if ($mostrarLimpo && $valorLimpo > 0)
                                                                <li>
                                                                    <span
                                                                        class="pcoded-badge label label-success mr-1">Limpo</span>
                                                                    R$ {{ number_format($valorLimpo, 2, ',', '.') }}
                                                                </li>
                                                            @endif
                                                            @if ($mostrarSujo && $valorSujo > 0)
                                                                <li>
                                                                    <span
                                                                        class="pcoded-badge label label-danger mr-1">Sujo</span>
                                                                    R$ {{ number_format($valorSujo, 2, ',', '.') }}
                                                                </li>
                                                            @endif
                                                            @if ($temDesconto)
                                                                <li>
                                                                    <span
                                                                        class="pcoded-badge label label-warning mr-1">Desconto
                                                                        Aplicado</span>
                                                                    -R$
                                                                    {{ number_format($registro->desconto_valor, 2, ',', '.') }}
                                                                    @if ($registro->desconto_motivo)
                                                                        <small class="text-muted d-block">
                                                                            {{ $registro->desconto_motivo }}
                                                                        </small>
                                                                    @endif
                                                                </li>
                                                            @endif
                                                            <li>
                                                                <small class="text-muted">
                                                                    Recebido em:
                                                                    {{ strtoupper($registro->pagamento_tipo ?? 'N/D') }}
                                                                    @if ($tipoPagamento === 'limpo')
                                                                        (apenas dinheiro limpo)
                                                                    @elseif ($tipoPagamento === 'sujo')
                                                                        (apenas dinheiro sujo)
                                                                    @elseif ($tipoPagamento === 'ambos')
                                                                        (dinheiro limpo e sujo)
                                                                    @endif
                                                                </small>
                                                            </li>
                                                        </ul>
                                                    @else
                                                        <p class="text-muted mb-0">
                                                            <small><i class="ti-info-alt"></i> Aguardando dados de
                                                                pagamento...</small>
                                                        </p>
                                                    @endif
                                                </div>
                                            @endif

                                            @if ($emAtendimento)
                                                <div class="pedido-resumo__secao">
                                                    <div class="pedido-resumo__titulo">
                                                        <i class="ti-headphone-alt text-info"></i> Atendimento
                                                    </div>
                                                    <ul class="pedido-resumo__lista">
                                                        @if (!empty($registro->data_entrega_estimada))
                                                            <li>
                                                                <strong>Entrega estimada:</strong>
                                                                {{ optional($registro->data_entrega_estimada)->format('d/m/Y') }}
                                                            </li>
                                                        @endif
                                                        @if (!empty($registro->responsavel_nome) || !empty($registro->usuario->nome))
                                                            <li>
                                                                <strong>Responsável:</strong>
                                                                {{ optional($registro->usuario)->nome ?? ($registro->responsavel_nome ?? 'Não definido') }}
                                                            </li>
                                                        @endif
                                                        @if (!empty($registro->observacao_atendimento))
                                                            <li>
                                                                <strong>Observações:</strong>
                                                                <small
                                                                    class="text-muted d-block">{{ $registro->observacao_atendimento }}</small>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <small class="text-info">
                                                                <i class="ti-clock"></i> Em atendimento desde
                                                                {{ optional($registro->updated_at)->format('d/m/Y H:i') ?? 'data não informada' }}
                                                            </small>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    @if ($canPerformActions)
                                        <td class="text-center align-middle">
                                            <div class="d-flex mt-2" style="gap: 0.5rem;">
                                                @can('acesso', 'venda.fila.edit')
                                                    @if ($registro->status !== FilaEspera::STATUS_CONCLUIDO && $registro->status !== FilaEspera::STATUS_CANCELADO)
                                                        <a href="{{ route('venda.fila.edit', $registro->id) }}"
                                                            class="btn btn-primary btn-out-dashed waves-effect waves-light flex-fill">
                                                            <i class="ti-pencil"></i> Editar
                                                        </a>
                                                    @endif
                                                @endcan
                                                @can('acesso', 'venda.fila.vender')
                                                    @if ($registro->status !== FilaEspera::STATUS_CONCLUIDO && $registro->status !== FilaEspera::STATUS_CANCELADO)
                                                        <a href="{{ route('venda.fila.vender', $registro->id) }}"
                                                            class="btn btn-success btn-out-dashed waves-effect waves-light flex-fill">
                                                            <i class="ti-shopping-cart"></i> Registrar Vendas
                                                        </a>
                                                    @endif
                                                @endcan
                                                @can('acesso', 'venda.fila.destroy')
                                                    @if ($registro->status !== FilaEspera::STATUS_CONCLUIDO && $registro->status !== FilaEspera::STATUS_CANCELADO)
                                                        <button type="button"
                                                            class="btn btn-danger btn-out-dashed waves-effect waves-light flex-fill"
                                                            onclick="confirmarExclusao({{ $registro->id }})">
                                                            <i class="ti-trash"></i> Excluir
                                                        </button>
                                                    @endif
                                                @endcan

                                                @if ($registro->status === FilaEspera::STATUS_CONCLUIDO)
                                                    <div class="btn btn-light flex-fill" style="cursor: default;">
                                                        <i class="ti-check text-success"></i> <small>Venda
                                                            Concluída</small>
                                                    </div>
                                                @endif

                                                @if ($registro->status === FilaEspera::STATUS_CANCELADO)
                                                    <div class="btn btn-light flex-fill" style="cursor: default;">
                                                        <i class="ti-close text-danger"></i> <small>Venda
                                                            Cancelada</small>
                                                    </div>
                                                @endif
                                            </div>
                                            @can('acesso', 'venda.fila.destroy')
                                                @if ($registro->status !== FilaEspera::STATUS_CONCLUIDO && $registro->status !== FilaEspera::STATUS_CANCELADO)
                                                    <form id="form-excluir-{{ $registro->id }}" class="d-none mt-2"
                                                        method="POST"
                                                        action="{{ route('venda.fila.destroy', $registro->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Nenhum pedido aguardando
                                        atendimento.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @php
                    $isPaginator = $fila instanceof \Illuminate\Contracts\Pagination\Paginator;
                    $totalRegistros = match (true) {
                        $fila instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator => $fila->total(),
                        $fila instanceof \Illuminate\Support\Collection => $fila->count(),
                        default => 0,
                    };
                @endphp
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted mb-2 mb-md-0">
                    Total exibido: <strong>{{ $totalRegistros }}</strong>
                </div>
                <div class="mb-2 mb-md-0">
                    @if ($isPaginator)
                        {{ $fila->appends(request()->query())->links('pagination::bootstrap-4') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        function confirmarExclusao(id) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success swal-btn-confirm',
                    cancelButton: 'btn btn-danger swal-btn-cancel'
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: 'Excluir pedido?',
                text: 'Essa ação não poderá ser desfeita.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sim, excluir',
                cancelButtonText: 'Cancelar',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('form-excluir-' + id);
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const listaPagamentos = document.querySelectorAll('.pedido-resumo__lista--pagamento li');
            listaPagamentos.forEach(function(item) {
                item.classList.add('mb-1');
            });
        });
    </script>

    <style>
        .pedido-resumo {
            max-width: 420px;
            margin: 0 auto;
            text-align: left;
        }

        .pedido-resumo__secao+.pedido-resumo__secao {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #e0e0e0;
        }

        .pedido-resumo__titulo {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #e5ac2f;
            margin-bottom: 0.35rem;
        }

        .pedido-resumo__lista {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .pedido-resumo__lista li {
            margin-bottom: 0.35rem;
        }

        .pedido-resumo__lista li:last-child {
            margin-bottom: 0;
        }

        .pedido-resumo__texto {
            margin-bottom: 0;
            color: #ffffff;
            line-height: 1.4;
        }

        .pedido-resumo__produto {
            font-weight: 600;
            color: #ffffff;
        }

        @media (min-width: 1200px) {
            .pedido-resumo {
                max-width: 520px;
            }
        }

        /* Espaçamento dos botões do SweetAlert */
        .swal-btn-confirm {
            margin-right: 20px !important;
        }

        .swal-btn-cancel {
            margin-left: 20px !important;
        }
    </style>
@endsection
