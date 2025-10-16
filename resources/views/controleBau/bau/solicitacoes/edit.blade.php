@extends('layouts.master', ['titulo' => 'Solicitacão #' . $solicitacao->id, 'subtitulo' => 'Ajuste e aprovacão'])

@php
    use App\Models\DiscordSolicitacao;
    use Illuminate\Support\Str;

    $statusLabels = [
        DiscordSolicitacao::STATUS_PENDENTE => 'Pendente',
        DiscordSolicitacao::STATUS_AJUSTE => 'Em ajuste',
        DiscordSolicitacao::STATUS_APROVADA => 'Aprovada',
        DiscordSolicitacao::STATUS_REJEITADA => 'Rejeitada',
    ];

    $tiposLancamento = [
        'ENTRADA' => 'Entrada',
        'SAIDA' => 'Saida',
        'TRANSFERENCIA' => 'Transferencia',
    ];

    $linhas = collect($solicitacao->itens ?? [])->map(function ($item) {
        return [
            'itens_id' => data_get($item, 'itens_id'),
            'quantidade' => data_get($item, 'quantidade', 1),
            'bau_destino_id' => data_get($item, 'bau_destino_id'),
            'bau_origem_id' => data_get($item, 'bau_origem_id'),
        ];
    });

    if ($linhas->isEmpty()) {
        $linhas = collect([
            [
                'itens_id' => null,
                'quantidade' => 1,
                'bau_destino_id' => null,
                'bau_origem_id' => null,
            ],
        ]);
    }

    $bloquearEdicao = in_array(
        $solicitacao->status,
        [DiscordSolicitacao::STATUS_APROVADA, DiscordSolicitacao::STATUS_REJEITADA],
        true,
    );
@endphp

@section('conteudo')
    <style>
        .anexo-thumb-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }

        .anexo-thumb-card {
            width: 120px;
            max-width: 100%;
        }

        .anexo-thumb-card .anexo-thumb-trigger {
            display: block;
            padding: 0;
            border: none;
            background: transparent;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
            transition: transform 0.2s ease-in-out;
        }

        .anexo-thumb-card .anexo-thumb-trigger:hover {
            transform: translateY(-2px);
        }

        .anexo-thumb-card img {
            width: 100%;
            height: 90px;
            object-fit: cover;
            display: block;
        }

        .anexo-thumb-card .anexo-info {
            margin-top: 0.35rem;
            font-size: 0.78rem;
            line-height: 1.15;
        }

        .anexo-thumb-card .anexo-info a {
            font-size: 0.8rem;
            font-weight: 600;
        }

        .anexo-thumb-card .badge {
            font-size: 0.7rem;
        }

        @media (max-width: 575px) {
            .anexo-thumb-card {
                width: calc(50% - 0.5rem);
            }
        }
    </style>

    @if (!empty($alertas))
        <div class="col-sm-12 mb-3">
            @foreach ($alertas as $mensagem)
                <span class="label label-danger m-r-5 d-inline-block alerta-item">
                    <i class="ti-alert"></i> {{ $mensagem }}
                </span>
            @endforeach
        </div>
    @endif

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h3 class="mb-0">Resumo da solicitacão</h3>
                    </div>
                    <div class="col-md-3 text-md-right mt-2 mt-md-0">
                        <a id="voltar-btn" type="button"
                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('bau.lancamentos.solicitacoes.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-block">
                <div class="row">
                    <div class="col-md-4">
                        <strong>Status atual:</strong>
                        <span
                            class="badge badge-inverse">{{ $statusLabels[$solicitacao->status] ?? $solicitacao->status }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Tipo solicitado:</strong>
                        <span class="badge badge-primary">{{ $solicitacao->tipo }}</span>
                    </div>
                    <div class="col-md-4">
                        <strong>Recebido:</strong>
                        {{ optional($solicitacao->created_at)->format('d/m/Y H:i') }}
                    </div>
                </div>
                @php
                    $anexos = collect(data_get($solicitacao->payload, 'anexos', []))->filter(
                        fn($anexo) => is_array($anexo) && data_get($anexo, 'url'),
                    );
                @endphp
                <div class="row mt-3">
                    <div class="col-md-4">
                        <strong>Autor:</strong> {{ $solicitacao->discord_username ?? '-' }}
                    </div>
                    <div class="col-md-4">
                        <strong>Anexos:</strong> {{ $anexos->count() }}
                    </div>
                </div>
                @if ($anexos->isNotEmpty())
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Lista de anexos:</strong>
                            <div class="anexo-thumb-grid">
                                @foreach ($anexos as $indice => $anexo)
                                    @php
                                        $nomeAnexo =
                                            data_get($anexo, 'filename') ??
                                            (data_get($anexo, 'name') ?? 'Arquivo ' . ($indice + 1));
                                        $urlAnexo = data_get($anexo, 'url') ?? data_get($anexo, 'proxy_url');
                                        $tamanho = data_get($anexo, 'size');
                                        $contentType = Str::lower((string) data_get($anexo, 'content_type', ''));
                                        $extensao = Str::lower(
                                            pathinfo(
                                                parse_url($urlAnexo ?? '', PHP_URL_PATH) ?? '',
                                                PATHINFO_EXTENSION,
                                            ),
                                        );
                                        $isImagem =
                                            Str::startsWith($contentType, 'image/') ||
                                            in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                                    @endphp
                                    <div class="anexo-thumb-card">
                                        @if ($isImagem)
                                            <button type="button" class="anexo-thumb-trigger" data-toggle="modal"
                                                data-target="#anexoModal{{ $indice }}">
                                                <img src="{{ $urlAnexo }}" alt="{{ $nomeAnexo }}"
                                                    onerror="this.src='https://via.placeholder.com/300x200?text=Anexo';">
                                            </button>
                                        @else
                                            <a href="{{ $urlAnexo }}" target="_blank" rel="noopener noreferrer"
                                                class="btn btn-light btn-sm btn-block d-flex align-items-center justify-content-center m-0">
                                                <i class="ti-download mr-1"></i> Abrir arquivo
                                            </a>
                                        @endif
                                        <div class="anexo-info">
                                            <a href="{{ $urlAnexo }}" target="_blank" rel="noopener noreferrer">
                                                {{ Str::limit($nomeAnexo, 28) }}
                                            </a>
                                            @if ($tamanho)
                                                <div class="text-muted">
                                                    {{ number_format($tamanho / 1024, 2) }} KB
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @foreach ($anexos as $indice => $anexo)
                        @php
                            $nomeAnexo =
                                data_get($anexo, 'filename') ??
                                (data_get($anexo, 'name') ?? 'Arquivo ' . ($indice + 1));
                            $urlAnexo = data_get($anexo, 'url') ?? data_get($anexo, 'proxy_url');
                            $contentType = Str::lower((string) data_get($anexo, 'content_type', ''));
                            $extensao = Str::lower(
                                pathinfo(parse_url($urlAnexo ?? '', PHP_URL_PATH) ?? '', PATHINFO_EXTENSION),
                            );
                            $isImagem =
                                Str::startsWith($contentType, 'image/') ||
                                in_array($extensao, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                        @endphp
                        @if ($isImagem)
                            <div class="modal fade" id="anexoModal{{ $indice }}" tabindex="-1" role="dialog"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $nomeAnexo }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0">
                                            <img src="{{ $urlAnexo }}" alt="{{ $nomeAnexo }}"
                                                class="img-fluid w-100">
                                        </div>
                                        <div class="modal-footer">
                                            <a href="{{ $urlAnexo }}" target="_blank" rel="noopener noreferrer"
                                                class="btn btn-primary btn-out-dashed waves-effect waves-light">
                                                <i class="ti-new-window"></i> Abrir em nova guia
                                            </a>
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endif
                @if ($solicitacao->observacao)
                    <div class="row mt-3">
                        <div class="col-12">
                            <strong>Observacoes:</strong>
                            <p class="mb-0">{{ $solicitacao->observacao }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <h3 class="mb-1">Ajustar itens e detalhes</h3>
                        <p class="text-muted mb-0">Salve os ajustes antes de aprovar.</p>
                    </div>
                </div>
            </div>
            <form class="form-material" action="{{ route('bau.lancamentos.solicitacoes.update', $solicitacao) }}"
                method="POST">
                @csrf
                @method('PUT')
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-12 col-lg-6">
                            <select class="form-control" disabled>
                                @foreach ($tiposLancamento as $valor => $descricao)
                                    <option value="{{ $valor }}" @selected($solicitacao->tipo === $valor)>{{ $descricao }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Tipo</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-12 col-lg-6">
                            <textarea name="observacao" class="form-control" rows="3" placeholder="Observacoes">{{ old('observacao', $solicitacao->observacao) }}</textarea>
                            <span class="form-bar"></span>
                            <label class="float-label">Observacoes</label>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabela-itens">
                                    <thead>
                                        <tr>
                                            <th style="width:45%">Item</th>
                                            <th style="width:15%" class="text-center">Quantidade</th>
                                            <th style="width:20%">Bau destino</th>
                                            <th style="width:20%">Bau origem</th>
                                            @unless ($bloquearEdicao)
                                                <th style="width:5%" class="text-center">Acoes</th>
                                            @endunless
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($linhas as $i => $linha)
                                            <tr data-index="{{ $i }}">
                                                <td>
                                                    <select name="itens[{{ $i }}][itens_id]"
                                                        class="form-control select2">
                                                        <option value="">Selecione</option>
                                                        @foreach ($itens as $it)
                                                            <option value="{{ $it->id }}"
                                                                @selected($linha['itens_id'] == $it->id)>
                                                                {{ $it->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="text" name="itens[{{ $i }}][quantidade]"
                                                        class="form-control" value="{{ $linha['quantidade'] }}">
                                                </td>
                                                <td>
                                                    <select name="itens[{{ $i }}][bau_destino_id]"
                                                        class="form-control select2"
                                                        {{ $solicitacao->tipo === 'SAIDA' ? 'disabled' : '' }}>
                                                        <option value="">Selecione</option>
                                                        @foreach ($baus as $bau)
                                                            <option value="{{ $bau->id }}"
                                                                @selected($linha['bau_destino_id'] == $bau->id)>
                                                                {{ $bau->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="itens[{{ $i }}][bau_origem_id]"
                                                        class="form-control select2"
                                                        {{ $solicitacao->tipo === 'ENTRADA' ? 'disabled' : '' }}>
                                                        <option value="">Selecione</option>
                                                        @foreach ($baus as $bau)
                                                            <option value="{{ $bau->id }}"
                                                                @selected($linha['bau_origem_id'] == $bau->id)>
                                                                {{ $bau->nome }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                @unless ($bloquearEdicao)
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-danger remover-item">
                                                            <i class="ti-trash"></i> Remover
                                                        </button>
                                                    </td>
                                                @endunless
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @unless ($bloquearEdicao)
                                <button type="button" class="btn btn-outline-secondary btn-sm adicionar-item">
                                    <i class="ti-plus"></i> Adicionar item
                                </button>
                            @endunless
                        </div>
                    </div>
                    <template id="linha-item-template">
                        <tr data-index="__INDEX__">
                            <td>
                                <select name="itens[__INDEX__][itens_id]" class="form-control select2">
                                    <option value="">Selecione</option>
                                    @foreach ($itens as $it)
                                        <option value="{{ $it->id }}">{{ $it->nome }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center">
                                <input type="text" name="itens[__INDEX__][quantidade]" class="form-control"
                                    value="1">
                            </td>
                            <td>
                                <select name="itens[__INDEX__][bau_destino_id]" class="form-control select2"
                                    {{ $solicitacao->tipo === 'SAIDA' ? 'disabled' : '' }}>
                                    <option value="">Selecione</option>
                                    @foreach ($baus as $bau)
                                        <option value="{{ $bau->id }}">{{ $bau->nome }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="itens[__INDEX__][bau_origem_id]" class="form-control select2"
                                    {{ $solicitacao->tipo === 'ENTRADA' ? 'disabled' : '' }}>
                                    <option value="">Selecione</option>
                                    @foreach ($baus as $bau)
                                        <option value="{{ $bau->id }}">{{ $bau->nome }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="text-center">
                                @unless ($bloquearEdicao)
                                    <button type="button" class="btn btn-sm btn-danger remover-item">
                                        <i class="ti-trash"></i> Remover
                                    </button>
                                @endunless
                            </td>
                        </tr>
                    </template>
                </div>
                @unless ($bloquearEdicao)
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary btn-out-dashed waves-effect waves-light">
                            <i class="ti-save"></i> Salvar ajustes
                        </button>
                    </div>
                @endunless
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Aprovar ou rejeitar</h3>
            </div>
            <div class="card-block">
                <p class="text-muted">Depois de salvar os ajustes, utilize os botoes abaixo para finalizar a solicitacão.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    @unless ($bloquearEdicao)
                        <form action="{{ route('bau.lancamentos.solicitacoes.aprovar', $solicitacao) }}" method="POST"
                            class="mr-2">
                            @csrf
                            <button type="submit"
                                class="btn btn-success btn-out-dashed waves-effect waves-light confirmar-aprovacao">
                                <i class="ti-check"></i> Aprovar e lancar
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-out-dashed waves-effect waves-light"
                            data-toggle="modal" data-target="#modalRejeitar">
                            <i class="ti-close"></i> Rejeitar
                        </button>
                    @else
                        <span class="text-muted">Solicitacao finalizada (status:
                            {{ $statusLabels[$solicitacao->status] ?? $solicitacao->status }}).</span>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRejeitar" tabindex="-1" role="dialog" aria-labelledby="modalRejeitarLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('bau.lancamentos.solicitacoes.rejeitar', $solicitacao) }}"
                class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRejeitarLabel">Rejeitar solicitacão</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="motivo">Informe o motivo</label>
                        <textarea name="motivo" id="motivo" rows="4" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Confirmar rejeicão</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabela = document.querySelector('#tabela-itens tbody');
            const addBtn = document.querySelector('.adicionar-item');
            const template = document.getElementById('linha-item-template');

            if (!tabela || !addBtn || !template) {
                return;
            }

            function aplicarSelect2(scope) {
                var alvo = scope || tabela;
                if (!alvo) return;
                if (window.AppSelect2 && typeof window.AppSelect2.initAll === 'function') {
                    window.AppSelect2.initAll(alvo);
                } else if (window.jQuery && window.jQuery.fn.select2) {
                    window.jQuery(alvo).find('select.form-control.select2').select2({
                        width: '100%'
                    });
                }
            }

            function reindexar() {
                Array.from(tabela.querySelectorAll('tr')).forEach(function(linha, indice) {
                    linha.setAttribute('data-index', indice);
                    linha.querySelectorAll('[name]').forEach(function(campo) {
                        const nome = campo.getAttribute('name');
                        if (!nome) return;
                        campo.setAttribute('name', nome.replace(/itens\[\d+\]/, 'itens[' + indice +
                            ']'));
                    });
                });
            }

            function criarLinha(indice) {
                const fragmento = document.importNode(template.content, true);
                fragmento.querySelectorAll('[name]').forEach(function(campo) {
                    const nome = campo.getAttribute('name');
                    if (!nome) return;
                    campo.setAttribute('name', nome.replace(/__INDEX__/g, indice));
                });
                const linha = fragmento.querySelector('tr');
                linha.setAttribute('data-index', indice);
                tabela.appendChild(fragmento);
                const linhaInserida = tabela.querySelector('tr:last-child');
                aplicarSelect2(linhaInserida);
                const botao = linhaInserida.querySelector('.remover-item');
                if (botao) {
                    botao.addEventListener('click', function() {
                        linhaInserida.remove();
                        reindexar();
                    });
                }
                return linhaInserida;
            }

            tabela.querySelectorAll('.remover-item').forEach(function(botao) {
                botao.addEventListener('click', function() {
                    const linha = botao.closest('tr');
                    if (!linha) return;
                    linha.remove();
                    reindexar();
                });
            });

            addBtn.addEventListener('click', function() {
                const indice = tabela.querySelectorAll('tr').length;
                criarLinha(indice);
            });

            aplicarSelect2(tabela);

            const botaoAprovar = document.querySelector('.confirmar-aprovacao');
            if (botaoAprovar) {
                botaoAprovar.addEventListener('click', function(evento) {
                    const form = botaoAprovar.closest('form');
                    if (!form) return;

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        evento.preventDefault();
                        window.Swal.fire({
                            title: 'Aprovar solicitacao',
                            text: 'Deseja realmente aprovar esta solicitacao?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Aprovar',
                            cancelButtonText: 'Cancelar',
                            buttonsStyling: false,
                            customClass: {
                                confirmButton: 'btn btn-success mr-2',
                                cancelButton: 'btn btn-secondary'
                            }
                        }).then(function(resultado) {
                            if (resultado.isConfirmed) {
                                form.submit();
                            }
                        });
                    }
                });
            }
        });
    </script>
@endsection
