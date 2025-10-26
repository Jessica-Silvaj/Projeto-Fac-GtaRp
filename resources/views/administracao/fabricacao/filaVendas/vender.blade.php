@extends('layouts.master', ['titulo' => 'Processar Venda', 'subtitulo' => 'Gerenciar pedido da fila'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>Venda para {{ optional($fila->organizacao)->nome ?? 'Organizacao nao informada' }}</h3>
                        <small class="text-muted">Pedido #{{ $fila->id }} &bullet; Aberto em
                            {{ optional($fila->data_pedido)->format('d/m/Y H:i') }}</small>
                    </div>
                    <div class="col-md-2 text-right">
                        <a class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('venda.fila.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" method="POST" action="{{ route('venda.fila.vender.processar', $fila->id) }}">
                @csrf
                <div class="card-block">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Solicitante:</strong> {{ $fila->nome }}
                            </div>
                            <div class="mb-2">
                                <strong>Descricao original:</strong>
                                <div class="border rounded p-2 bg-light">
                                    {!! nl2br(e($fila->pedido)) !!}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <strong>Entrega estimada:</strong>
                                {{ optional($fila->data_entrega_estimada)->format('d/m/Y H:i') ?? 'Sem previsao' }}
                            </div>
                            <div class="mb-2">
                                <strong>Status atual:</strong>
                                <span class="badge badge-info text-uppercase">{{ $fila->status }}</span>
                            </div>
                        </div>
                    </div>
                    @php
                        $descontoAtual = old('desconto_aplicado', $fila->desconto_aplicado ? 'sim' : 'nao');

                        // Determinar a tabela de preço baseada nos itens existentes
                        $tabelaSelecionada = old('tabela_preco_global');
                        if ($tabelaSelecionada === null && $fila->itens->isNotEmpty()) {
                            // Pegar a tabela do primeiro item (assumindo que todos usam a mesma)
                            $tabelaSelecionada = $fila->itens->first()->tabela_preco ?? 'padrao';
                        } elseif ($tabelaSelecionada === null) {
                            $tabelaSelecionada = 'padrao';
                        }
                    @endphp
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="border rounded p-3 mb-3">
                                <h5 class="h6 text-uppercase text-muted mb-3">Configuracoes da venda</h5>
                                <div class="form-row">
                                    <div class="form-group form-default form-static-label col-md-6">
                                        <select name="responsavel" id="responsavel"
                                            class="form-control @error('responsavel') is-invalid @enderror">
                                            <option value="">Selecione</option>
                                            @foreach ($responsaveis as $responsavel)
                                                <option value="{{ $responsavel->id }}"
                                                    {{ old('responsavel', $fila->usuario_id) == $responsavel->id ? 'selected' : '' }}>
                                                    {{ $responsavel->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="form-bar"></span>
                                        <label class="float-label">Responsavel</label>
                                        @error('responsavel')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group form-default form-static-label col-md-6">
                                        <select name="status" id="status"
                                            class="form-control @error('status') is-invalid @enderror">
                                            @foreach ($statusOptions as $valor => $texto)
                                                <option value="{{ $valor }}"
                                                    {{ old('status', $fila->status) === $valor ? 'selected' : '' }}>
                                                    {{ $texto }}</option>
                                            @endforeach
                                        </select>
                                        <span class="form-bar"></span>
                                        <label class="float-label">Status apos processamento</label>
                                        @error('status')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group form-default form-static-label col-md-6">
                                        <select name="tabela_preco_global" id="tabela_preco_global"
                                            class="form-control @error('tabela_preco_global') is-invalid @enderror">
                                            <option value="padrao" {{ $tabelaSelecionada === 'padrao' ? 'selected' : '' }}>
                                                Padrao</option>
                                            <option value="desconto"
                                                {{ $tabelaSelecionada === 'desconto' ? 'selected' : '' }}>Desconto</option>
                                            <option value="alianca"
                                                {{ $tabelaSelecionada === 'alianca' ? 'selected' : '' }}>Alianca</option>
                                        </select>
                                        <span class="form-bar"></span>
                                        <label class="float-label">Tabela de preco</label>
                                        @error('tabela_preco_global')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div id="secao-itens-pedido" class="border rounded p-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="h6 text-uppercase text-muted mb-0">Itens do pedido</h5>
                                    <button type="button" id="add-item"
                                        class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                                        <i class="ti-plus"></i> Adicionar item
                                    </button>
                                </div>
                                <div class="table-responsive mb-0">
                                    <table class="table table-bordered mb-0" id="tabela-itens">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="col-md-5">Produto</th>
                                                <th class="col-md-2 text-center">Qtd</th>
                                                <th class="col-md-4">Observacao</th>
                                                <th class="col-md-1 text-center">Remover</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $itensVenda = old(
                                                    'produto_id',
                                                    $fila->itens->pluck('produto_id')->toArray(),
                                                );
                                                $quantidadesVenda = old(
                                                    'quantidade',
                                                    $fila->itens->pluck('quantidade')->toArray(),
                                                );
                                                $observacoesVenda = old(
                                                    'item_observacao',
                                                    $fila->itens->pluck('observacao')->toArray(),
                                                );
                                                $totalLinhas = max(count($itensVenda), 1);
                                            @endphp
                                            @for ($i = 0; $i < $totalLinhas; $i++)
                                                <tr>
                                                    <td>
                                                        <select name="produto_id[]"
                                                            class="form-control produto-select @error('produto_id.' . $i) is-invalid @enderror">
                                                            <option value="">Selecione</option>
                                                            @foreach ($produtos as $produto)
                                                                <option value="{{ $produto->id }}"
                                                                    {{ ($itensVenda[$i] ?? null) == $produto->id ? 'selected' : '' }}>
                                                                    {{ $produto->nome }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('produto_id.' . $i)
                                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="number" min="1" name="quantidade[]"
                                                            class="form-control text-center @error('quantidade.' . $i) is-invalid @enderror"
                                                            value="{{ $quantidadesVenda[$i] ?? 1 }}">
                                                        @error('quantidade.' . $i)
                                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                        @enderror
                                                    </td>
                                                    <td>
                                                        <input type="text" name="item_observacao[]"
                                                            class="form-control @error('item_observacao.' . $i) is-invalid @enderror"
                                                            value="{{ $observacoesVenda[$i] ?? '' }}"
                                                            placeholder="Opcional">
                                                        @error('item_observacao.' . $i)
                                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                        @enderror
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger btn-out-dashed remove-item">
                                                            <i class="ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endfor
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="border rounded p-3 mb-0">
                                <h5 class="h6 text-uppercase text-muted mb-3">Observacoes da venda</h5>
                                <div class="form-group form-default form-static-label mb-0">
                                    <textarea name="observacao" id="observacao" rows="3"
                                        class="form-control @error('observacao') is-invalid @enderror"
                                        placeholder="Anote combinacoes, instrucoes ou detalhes importantes">{{ old('observacao') }}</textarea>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Observacoes adicionais</label>
                                    @error('observacao')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="border rounded p-3 mb-3" id="quadro-tabela-container">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="h6 text-uppercase mb-0">Valores da tabela selecionada</h5>
                                    <span class="badge badge-light border font-weight-semibold text-uppercase"
                                        id="quadro-tabela-label">-</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0" id="quadro-tabela-preco">
                                        <thead>
                                            <tr>
                                                <th>Produto</th>
                                                <th class="text-right">Valor limpo</th>
                                                <th class="text-right">Valor sujo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-muted text-center">Selecione uma tabela
                                                    para visualizar.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="border rounded p-3 mb-3">
                                <h5 class="h6 text-uppercase text-muted mb-3">Totais e pagamento</h5>
                                @php
                                    $pagamentoTipo = old('pagamento_tipo');
                                    if ($pagamentoTipo === null) {
                                        // Primeiro, verificar se há tipo de pagamento salvo
                                        if (!empty($fila->pagamento_tipo)) {
                                            $pagamentoTipo = $fila->pagamento_tipo;
                                        } else {
                                            // Inferir baseado nos valores (lógica antiga como fallback)
                                            $temLimpo = ($fila->dinheiro_limpo ?? 0) > 0;
                                            $temSujo = ($fila->dinheiro_sujo ?? 0) > 0;
                                            if ($temLimpo && $temSujo) {
                                                $pagamentoTipo = 'ambos';
                                            } elseif ($temSujo) {
                                                $pagamentoTipo = 'sujo';
                                            } else {
                                                $pagamentoTipo = 'limpo';
                                            }
                                        }
                                    }

                                    // Determinar valores a exibir nos campos
                                    // Os campos devem sempre mostrar os valores que o cliente deve pagar (valores finais)
                                    $valorLimpoExibir = $fila->dinheiro_limpo;
                                    $valorSujoExibir = $fila->dinheiro_sujo;

                                    // Se o pagamento foi específico (não ambos), zerar o campo não usado para evitar confusão
                                    if ($pagamentoTipo === 'limpo') {
                                        $valorSujoExibir = 0;
                                    } elseif ($pagamentoTipo === 'sujo') {
                                        $valorLimpoExibir = 0;
                                    }
                                @endphp
                                <div class="form-group form-default form-static-label">
                                    <input type="text" name="dinheiro_limpo" id="dinheiro_limpo"
                                        class="form-control @error('dinheiro_limpo') is-invalid @enderror"
                                        value="{{ old('dinheiro_limpo', $valorLimpoExibir) }}"
                                        title="Duplo clique para recalcular automaticamente">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Valor limpo a receber</label>
                                    @error('dinheiro_limpo')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                    <small class="text-info d-none" id="dinheiro-limpo-hint">
                                        <i class="ti-info-alt"></i> Duplo clique para recalcular automaticamente
                                    </small>
                                </div>
                                <div class="form-group form-default form-static-label">
                                    <input type="text" name="dinheiro_sujo" id="dinheiro_sujo"
                                        class="form-control @error('dinheiro_sujo') is-invalid @enderror"
                                        value="{{ old('dinheiro_sujo', $valorSujoExibir) }}"
                                        title="Duplo clique para recalcular automaticamente">
                                    <span class="form-bar"></span>
                                    <label class="float-label">Valor sujo a receber</label>
                                    @error('dinheiro_sujo')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                    <small class="text-info d-none" id="dinheiro-sujo-hint">
                                        <i class="ti-info-alt"></i> Duplo clique para recalcular automaticamente
                                    </small>
                                </div>
                                <div class="form-group">
                                    <label class="font-weight-semibold d-block mb-2">Pagamento recebido em</label>
                                    <div class="d-flex">
                                        <label class="mr-3 mb-0">
                                            <input type="radio" name="pagamento_tipo" value="limpo"
                                                {{ $pagamentoTipo === 'limpo' ? 'checked' : '' }}>
                                            <span class="ml-1">Limpo</span>
                                        </label>
                                        <label class="mr-3 mb-0">
                                            <input type="radio" name="pagamento_tipo" value="sujo"
                                                {{ $pagamentoTipo === 'sujo' ? 'checked' : '' }}>
                                            <span class="ml-1">Sujo</span>
                                        </label>
                                        <label class="mb-0">
                                            <input type="radio" name="pagamento_tipo" value="ambos"
                                                {{ $pagamentoTipo === 'ambos' ? 'checked' : '' }}>
                                            <span class="ml-1">Ambos</span>
                                        </label>
                                    </div>
                                    @error('pagamento_tipo')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group form-default form-static-label">
                                    <select name="desconto_aplicado" id="desconto_aplicado"
                                        class="form-control @error('desconto_aplicado') is-invalid @enderror">
                                        <option value="nao" {{ $descontoAtual === 'nao' ? 'selected' : '' }}>Sem
                                            desconto</option>
                                        <option value="sim" {{ $descontoAtual === 'sim' ? 'selected' : '' }}>Aplicar
                                            desconto</option>
                                    </select>
                                    <span class="form-bar"></span>
                                    <label class="float-label">Desconto</label>
                                    @error('desconto_aplicado')
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="desconto-detalhes {{ $descontoAtual === 'sim' ? '' : 'd-none' }}">
                                    <div class="form-group form-default form-static-label">
                                        <input type="text" name="desconto_valor" id="desconto_valor"
                                            class="form-control @error('desconto_valor') is-invalid @enderror"
                                            value="{{ old('desconto_valor', $fila->desconto_valor) }}">
                                        <span class="form-bar"></span>
                                        <label class="float-label">Valor do desconto</label>
                                        @error('desconto_valor')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    <div class="form-group form-default form-static-label mb-0">
                                        <input type="text" name="desconto_motivo" id="desconto_motivo"
                                            class="form-control @error('desconto_motivo') is-invalid @enderror"
                                            value="{{ old('desconto_motivo', $fila->desconto_motivo) }}"
                                            placeholder="Explique o motivo">
                                        <span class="form-bar"></span>
                                        <label class="float-label">Motivo do desconto</label>
                                        @error('desconto_motivo')
                                            <small class="text-danger d-block mt-1">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="border rounded p-3 mb-3" id="totais-cobrar">
                                <h5 class="h6 text-uppercase text-muted mb-3">Resumo de valores</h5>
                                <div id="totais-cobrar-text" class="resumo-valores text-muted">
                                    Nenhum produto selecionado.
                                </div>
                            </div>
                            <div class="border rounded p-3 mb-3" id="materiais-container">
                                <h5 class="h6 text-uppercase text-muted mb-3">Materiais necessarios</h5>
                                <table class="table table-sm table-striped mb-0" id="materiais-necessarios">
                                    <thead>
                                        <tr>
                                            <th>Material</th>
                                            <th class="text-right">Quantidade</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="2" class="text-muted text-center">Selecione produtos e
                                                quantidades
                                                para visualizar.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                        <i class="ti-save"></i> Registrar venda
                    </button>
                    <a href="{{ route('venda.fila.vender', $fila->id) }}"
                        class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light">
                        <i class="ti-close"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        const produtosData = @json($produtosInfo ?? []);

        // Valores pré-existentes para inicialização correta
        const valoresPreExistentes = {
            limpo: {{ $valorLimpoExibir ?? 0 }},
            sujo: {{ $valorSujoExibir ?? 0 }},
            desconto: {{ $fila->desconto_valor ?? 0 }},
            descontoAplicado: {{ $fila->desconto_aplicado ? 'true' : 'false' }},
            tabelaPreco: '{{ $tabelaSelecionada }}',
            tipoPagamento: '{{ $pagamentoTipo }}'
        };

        $(function() {
            const $tabelaGlobal = $('#tabela_preco_global');
            const $descontoSelect = $('#desconto_aplicado');
            const $descontoValor = $('#desconto_valor');
            const $dinheiroLimpo = $('#dinheiro_limpo');
            const $dinheiroSujo = $('#dinheiro_sujo');
            const $pagamentoRadios = $('input[name="pagamento_tipo"]');
            const $materiaisBody = $('#materiais-necessarios tbody');
            const $totaisCobrarText = $('#totais-cobrar-text');
            const $quadroTabelaLabel = $('#quadro-tabela-label');
            const $quadroTabelaBody = $('#quadro-tabela-preco tbody');
            const $tabelaItens = $('#tabela-itens');
            const $tabelaItensBody = $tabelaItens.find('tbody');
            const $statusSelect = $('#status');
            const $secaoItens = $('#secao-itens-pedido');
            const $materiaisContainer = $('#materiais-container');
            const tabelaLabels = {
                padrao: 'PADRAO',
                desconto: 'DESCONTO',
                alianca: 'ALIANCA',
            };
            let totaisAtuais = {
                totalLimpo: 0,
                totalSujo: 0,
                totalLimpoFinal: 0,
                totalSujoFinal: 0,
            };
            let resumoEstado = {
                itensValidos: 0,
                descontoAplicado: false,
                descontoValor: 0,
            };
            const currencyFormatter = new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });

            function parseValorNumerico(valor) {
                if (typeof valor === 'number') {
                    return Number.isFinite(valor) ? valor : 0;
                }
                if (typeof valor !== 'string') {
                    return 0;
                }
                const texto = valor.trim();
                if (!texto) {
                    return 0;
                }
                const apenasNumeros = texto.replace(/[^0-9,.-]/g, '');
                let normalizado = apenasNumeros;
                if (normalizado.includes(',')) {
                    normalizado = normalizado.replace(/\./g, '').replace(',', '.');
                } else {
                    const dotCount = (normalizado.match(/\./g) || []).length;
                    if (dotCount > 0) {
                        const lastDot = normalizado.lastIndexOf('.');
                        const decimais = normalizado.length - lastDot - 1;
                        if (dotCount > 1 || decimais > 2) {
                            normalizado = normalizado.replace(/\./g, '');
                        }
                    }
                }
                const parsed = parseFloat(normalizado);
                return Number.isNaN(parsed) ? 0 : parsed;
            }

            function formatCurrency(valor) {
                const numero = Number.isFinite(valor) ? valor : 0;
                return currencyFormatter.format(numero).replace(/,00$/, '');
            }

            function atualizarDesconto() {
                const valor = $descontoSelect.val();
                $('.desconto-detalhes').toggleClass('d-none', valor !== 'sim');
            }

            function obterProdutoInfo(produtoId) {
                if (!produtoId) {
                    return null;
                }
                return produtosData[String(produtoId)] || produtosData[produtoId] || null;
            }

            function formatQuantidade(valor) {
                if (!Number.isFinite(valor)) {
                    return '0';
                }
                return Number.isInteger(valor) ? String(valor) : valor.toFixed(2);
            }

            function obterTabelaSelecionada() {
                return ($tabelaGlobal.val() || 'padrao').toLowerCase();
            }

            function calcularUnidadesMateriais(produtoId, quantidade) {
                const info = obterProdutoInfo(produtoId);
                const loteMinimo = info ? Number(info.lote_minimo || 1) : 1;
                if (loteMinimo > 1) {
                    return Math.ceil(quantidade / loteMinimo) * loteMinimo;
                }
                return quantidade;
            }

            function aplicarMascaraMonetaria($campo, valorPadrao) {
                if (typeof valorPadrao === 'number') {
                    $campo.val(formatCurrency(valorPadrao));
                    return;
                }
                const textoAtual = ($campo.val() || '').trim();
                if (!textoAtual) {
                    $campo.val('');
                    return;
                }
                const valor = parseValorNumerico(textoAtual);
                $campo.val(formatCurrency(valor));
            }

            function atualizarCamposDinheiro(valorLimpoAuto, valorSujoAuto) {
                // Sempre atualizar os campos quando há mudança nos produtos ou desconto
                // mas preservar valores manualmente editados pelo usuário
                const limpoManual = $dinheiroLimpo.data('manualmenteEditado');
                const sujoManual = $dinheiroSujo.data('manualmenteEditado');

                // Se não foi editado manualmente OU se estamos recalculando por mudança de desconto
                if (!limpoManual || $dinheiroLimpo.data('recalcularPorDesconto')) {
                    aplicarMascaraMonetaria($dinheiroLimpo, valorLimpoAuto);
                    $dinheiroLimpo.data('recalcularPorDesconto', false);
                }

                if (!sujoManual || $dinheiroSujo.data('recalcularPorDesconto')) {
                    aplicarMascaraMonetaria($dinheiroSujo, valorSujoAuto);
                    $dinheiroSujo.data('recalcularPorDesconto', false);
                }
            }

            function obterValoresPagamento(totais) {
                const tipo = $('input[name="pagamento_tipo"]:checked').val() || 'limpo';
                let limpo = totais.totalLimpoFinal;
                let sujo = totais.totalSujoFinal;
                if (tipo === 'limpo') {
                    sujo = 0;
                } else if (tipo === 'sujo') {
                    limpo = 0;
                }
                return {
                    limpo: Math.max(0, limpo),
                    sujo: Math.max(0, sujo),
                };
            }

            function atualizarResumoMateriais(materiais) {
                $materiaisBody.empty();
                const nomes = Object.keys(materiais);
                if (!nomes.length) {
                    $materiaisBody.append(
                        '<tr><td colspan="2" class="text-muted text-center">Selecione produtos e quantidades para visualizar.</td></tr>'
                    );
                    return;
                }
                nomes
                    .sort(function(a, b) {
                        return a.localeCompare(b);
                    })
                    .forEach(function(nome) {
                        $materiaisBody.append(
                            '<tr>' +
                            '<td>' + nome + '</td>' +
                            '<td class="text-right">' + formatQuantidade(materiais[nome]) + '</td>' +
                            '</tr>'
                        );
                    });
            }

            function atualizarQuadroTabela() {
                const tabela = obterTabelaSelecionada();
                const label = tabelaLabels[tabela] || tabela.toUpperCase();
                $quadroTabelaLabel.text(label);
                $quadroTabelaBody.empty();
                const produtosLista = Object.values(produtosData || {});
                if (!produtosLista.length) {
                    $quadroTabelaBody.append(
                        '<tr><td colspan="3" class="text-muted text-center">Nenhum produto configurado.</td></tr>'
                    );
                    return;
                }
                produtosLista
                    .slice()
                    .sort(function(a, b) {
                        return (a.nome || '').localeCompare(b.nome || '');
                    })
                    .forEach(function(produto) {
                        const precos = produto.precos || {};
                        const selecionado = precos[tabela] || precos.padrao || {
                            limpo: 0,
                            sujo: 0
                        };
                        $quadroTabelaBody.append(
                            '<tr>' +
                            '<td>' + (produto.nome || '-') + '</td>' +
                            '<td class="text-right">' + formatCurrency(Number(selecionado.limpo) || 0) +
                            '</td>' +
                            '<td class="text-right">' + formatCurrency(Number(selecionado.sujo) || 0) +
                            '</td>' +
                            '</tr>'
                        );
                    });
            }

            function atualizarResumoTotais(dados) {
                if (!dados.itensValidos) {
                    $totaisCobrarText
                        .addClass('text-muted')
                        .html('Nenhum produto selecionado.');
                    return;
                }

                $totaisCobrarText.removeClass('text-muted');

                const tipo = dados.pagamentoTipo || 'limpo';
                const exibirLimpo = tipo === 'limpo' || tipo === 'ambos';
                const exibirSujo = tipo === 'sujo' || tipo === 'ambos';
                const partes = [];

                const linha = function(label, valor, muted) {
                    const classe = muted ? 'small' : 'font-weight-semibold';
                    return (
                        '<div class="d-flex justify-content-between align-items-center ' + classe + '">' +
                        '<span>' + label + '</span>' +
                        '<span>' + valor + '</span>' +
                        '</div>'
                    );
                };

                const card = function(titulo, badgeTexto, badgeClasse, linhas) {
                    const badgeClassFinal = badgeClasse || 'pcoded-badge label label-default';
                    return (
                        '<div class="resumo-card border rounded mb-3 shadow-sm bg-white">' +
                        '<div class="px-3 py-2 bg-light d-flex justify-content-between align-items-center">' +
                        '<span class="text-uppercase small">' + titulo + '</span>' +
                        '<span class="' + badgeClassFinal + '">' + badgeTexto + '</span>' +
                        '</div>' +
                        '<div class="px-3 py-3">' +
                        linhas.join('<div class="border-top my-2"></div>') +
                        '</div>' +
                        '</div>'
                    );
                };

                if (dados.descontoAplicado && dados.descontoValor > 0) {
                    partes.push(
                        '<div class="alert alert-warning border-warning mb-3 py-2 px-3">' +
                        '<div class="d-flex justify-content-between align-items-center">' +
                        '<span class="font-weight-semibold text-uppercase small">Desconto aplicado</span>' +
                        '<span class="font-weight-semibold text-danger">-' + formatCurrency(dados
                            .descontoValor) + '</span>' +
                        '</div>' +
                        '<small class="d-block mt-1">O abatimento prioriza o valor limpo.</small>' +
                        '</div>'
                    );
                }

                const sugerido = obterValoresPagamento(totaisAtuais);
                const informados = {
                    limpo: parseValorNumerico($dinheiroLimpo.val()),
                    sujo: parseValorNumerico($dinheiroSujo.val()),
                };
                if (exibirLimpo) {
                    const linhasLimpo = [
                        linha('Bruto', formatCurrency(dados.totalLimpo), true),
                        linha('Total a receber', formatCurrency(dados.totalLimpoFinal), false),
                    ];
                    partes.push(card('Valores limpos', 'LIMPO', 'pcoded-badge label label-success', linhasLimpo));

                    const linhasPagamentoLimpo = [
                        linha('Sugerido', formatCurrency(sugerido.limpo), true),
                        linha('Informado', formatCurrency(informados.limpo), false),
                    ];
                    partes.push(card('Pagamento limpo', 'RECEBIMENTO',
                        'pcoded-badge label label-info', linhasPagamentoLimpo));
                }

                if (exibirSujo) {
                    const linhasSujo = [
                        linha('Bruto', formatCurrency(dados.totalSujo), true),
                        linha('Total a receber', formatCurrency(dados.totalSujoFinal), false),
                    ];
                    partes.push(card('Valores sujos', 'SUJO', 'pcoded-badge label label-danger', linhasSujo));

                    const linhasPagamentoSujo = [
                        linha('Sugerido', formatCurrency(sugerido.sujo), true),
                        linha('Informado', formatCurrency(informados.sujo), false),
                    ];
                    partes.push(card('Pagamento sujo', 'RECEBIMENTO',
                        'pcoded-badge label label-warning', linhasPagamentoSujo));
                }

                if (tipo === 'ambos') {
                    const totalGeral = formatCurrency(dados.totalLimpoFinal + dados.totalSujoFinal);
                    partes.push(card('Resumo geral', 'TOTAL', 'pcoded-badge label label-primary', [
                        linha('Total a receber', totalGeral, false)
                    ]));
                }

                $totaisCobrarText.html(partes.join(''));
            }

            function renderResumoAtual() {
                atualizarResumoTotais({
                    itensValidos: resumoEstado.itensValidos,
                    descontoAplicado: resumoEstado.descontoAplicado,
                    descontoValor: resumoEstado.descontoValor,
                    totalLimpo: totaisAtuais.totalLimpo,
                    totalSujo: totaisAtuais.totalSujo,
                    totalLimpoFinal: totaisAtuais.totalLimpoFinal,
                    totalSujoFinal: totaisAtuais.totalSujoFinal,
                    pagamentoTipo: $('input[name="pagamento_tipo"]:checked').val() || 'limpo',
                });
            }

            function prepararLinha($linha) {
                const $quantidade = $linha.find('input[name="quantidade[]"]');
                const quantidadeAtual = parseInt($quantidade.val(), 10);
                if (!quantidadeAtual || quantidadeAtual < 1) {
                    $quantidade.val(1);
                }
            }

            function recalcularTotais() {
                atualizarQuadroTabela();
                const tabelaSelecionada = obterTabelaSelecionada();
                const descontoAtivo = $descontoSelect.val() === 'sim';
                const descontoValorBruto = descontoAtivo ? parseValorNumerico($descontoValor.val()) : 0;
                const descontoValor = Math.max(0, descontoValorBruto);
                let totalLimpo = 0;
                let totalSujo = 0;
                let itensValidos = 0;
                const materiais = {};
                $tabelaItensBody.find('tr').each(function() {
                    const $linha = $(this);
                    const produtoId = parseInt($linha.find('select[name="produto_id[]"]').val(), 10);
                    const quantidadeInformada = parseInt($linha.find('input[name="quantidade[]"]').val(),
                        10);
                    if (!produtoId || !quantidadeInformada || quantidadeInformada <= 0) {
                        return;
                    }
                    const produto = obterProdutoInfo(produtoId);
                    if (!produto) {
                        return;
                    }
                    itensValidos += 1;
                    const precos = produto.precos || {};
                    const precoTabela = precos[tabelaSelecionada] || precos.padrao || {
                        limpo: 0,
                        sujo: 0
                    };
                    const unitarioLimpo = Number(precoTabela.limpo) || 0;
                    const unitarioSujo = Number(precoTabela.sujo) || 0;
                    totalLimpo += unitarioLimpo * quantidadeInformada;
                    totalSujo += unitarioSujo * quantidadeInformada;
                    const quantidadeParaMateriais = calcularUnidadesMateriais(produtoId,
                        quantidadeInformada);
                    (produto.componentes || []).forEach(function(componente) {
                        const porUnidade = Number(componente.por_unidade || 0);
                        if (!porUnidade) {
                            return;
                        }
                        const totalMaterial = porUnidade * quantidadeParaMateriais;
                        const chave = componente.nome || 'Material';
                        materiais[chave] = (materiais[chave] || 0) + totalMaterial;
                    });
                });
                let totalLimpoFinal = totalLimpo;
                let totalSujoFinal = totalSujo;
                if (descontoValor > 0) {
                    if (descontoValor <= totalLimpoFinal) {
                        totalLimpoFinal -= descontoValor;
                    } else {
                        const restante = descontoValor - totalLimpoFinal;
                        totalLimpoFinal = 0;
                        totalSujoFinal = Math.max(0, totalSujoFinal - restante);
                    }
                }
                totaisAtuais = {
                    totalLimpo,
                    totalSujo,
                    totalLimpoFinal,
                    totalSujoFinal,
                };
                resumoEstado = {
                    itensValidos,
                    descontoAplicado: descontoAtivo && descontoValor > 0,
                    descontoValor,
                };
                const valoresPagamento = obterValoresPagamento(totaisAtuais);
                atualizarCamposDinheiro(valoresPagamento.limpo, valoresPagamento.sujo);
                atualizarResumoMateriais(materiais);
                renderResumoAtual();
            }
            // Marcar campos como editados se já têm valores (caso de edição)
            const temValorLimpo = parseValorNumerico($dinheiroLimpo.val()) > 0 || valoresPreExistentes.limpo > 0;
            const temValorSujo = parseValorNumerico($dinheiroSujo.val()) > 0 || valoresPreExistentes.sujo > 0;
            const temDesconto = parseValorNumerico($descontoValor.val()) > 0 || valoresPreExistentes.desconto > 0;

            if (temValorLimpo) {
                $dinheiroLimpo.data('manualmenteEditado', true);
            }
            if (temValorSujo) {
                $dinheiroSujo.data('manualmenteEditado', true);
            }
            if (temDesconto) {
                $descontoValor.data('manualmenteEditado', true);
            }
            [$dinheiroLimpo, $dinheiroSujo].forEach(function($campo) {
                aplicarMascaraMonetaria($campo);
                $campo.on('input', function() {
                    // Marcar como editado manualmente apenas se o usuário digitou
                    $campo.data('manualmenteEditado', true);
                    $campo.data('recalcularPorDesconto', false);
                    aplicarMascaraMonetaria($campo);
                    renderResumoAtual();
                });

                // Permitir que o usuário force recálculo com duplo clique
                $campo.on('dblclick', function() {
                    $campo.data('manualmenteEditado', false);
                    $campo.data('recalcularPorDesconto', true);

                    // Esconder dica visual
                    const hintId = $campo.attr('id') + '-hint';
                    $('#' + hintId).addClass('d-none');

                    recalcularTotais();
                });

                // Mostrar dica visual quando campo é editado manualmente
                $campo.on('input', function() {
                    const hintId = $campo.attr('id') + '-hint';
                    if ($campo.data('manualmenteEditado')) {
                        $('#' + hintId).removeClass('d-none');
                    }
                });
            });
            $pagamentoRadios.on('change', function() {
                $dinheiroLimpo.data('manualmenteEditado', false);
                $dinheiroSujo.data('manualmenteEditado', false);
                const valoresPagamento = obterValoresPagamento(totaisAtuais);
                atualizarCamposDinheiro(valoresPagamento.limpo, valoresPagamento.sujo);
                renderResumoAtual();
            });
            aplicarMascaraMonetaria($descontoValor);
            $descontoValor.on('input', function() {
                aplicarMascaraMonetaria($descontoValor);

                // Marcar para recalcular campos quando valor do desconto muda
                $dinheiroLimpo.data('recalcularPorDesconto', true);
                $dinheiroSujo.data('recalcularPorDesconto', true);

                recalcularTotais();
            });
            $('#add-item').on('click', function() {
                const linhaHtml = `
                    <tr>
                        <td>
                            <select name="produto_id[]" class="form-control produto-select">
                                <option value="">Selecione</option>
                                @foreach ($produtos as $produto)
                                    <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" min="1" name="quantidade[]" class="form-control text-center" value="1">
                        </td>
                        <td>
                            <input type="text" name="item_observacao[]" class="form-control" placeholder="Opcional">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-sm btn-danger btn-out-dashed remove-item">
                                <i class="ti-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                const $linha = $(linhaHtml);
                $tabelaItensBody.append($linha);
                prepararLinha($linha);
                recalcularTotais();
            });
            $tabelaItens.on('click', '.remove-item', function() {
                if ($tabelaItensBody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                    recalcularTotais();
                }
            });
            $tabelaItens.on('change', 'select[name="produto_id[]"]', function() {
                // Marcar para recalcular campos quando produtos mudam
                $dinheiroLimpo.data('recalcularPorDesconto', true);
                $dinheiroSujo.data('recalcularPorDesconto', true);

                prepararLinha($(this).closest('tr'));
                recalcularTotais();
            });

            $tabelaItens.on('input', 'input[name="quantidade[]"]', function() {
                // Marcar para recalcular campos quando quantidades mudam
                $dinheiroLimpo.data('recalcularPorDesconto', true);
                $dinheiroSujo.data('recalcularPorDesconto', true);

                recalcularTotais();
            });
            $descontoSelect.on('change', function() {
                // Marcar para recalcular campos quando desconto muda
                $dinheiroLimpo.data('recalcularPorDesconto', true);
                $dinheiroSujo.data('recalcularPorDesconto', true);

                atualizarDesconto();
                recalcularTotais();
            });
            $tabelaGlobal.on('change', function() {
                // Marcar para recalcular campos quando tabela de preço muda
                $dinheiroLimpo.data('recalcularPorDesconto', true);
                $dinheiroSujo.data('recalcularPorDesconto', true);

                $tabelaItensBody.find('tr').each(function() {
                    prepararLinha($(this));
                });
                recalcularTotais();
            });
            // Inicializar linhas existentes
            $tabelaItensBody.find('tr').each(function() {
                prepararLinha($(this));
            });

            // Função para controlar visibilidade dos itens baseado no status
            function controlarVisibilidadeItens() {
                const statusSelecionado = $statusSelect.val();
                const isCancelado = statusSelecionado === '{{ \App\Models\FilaEspera::STATUS_CANCELADO }}';

                if (isCancelado) {
                    $secaoItens.hide();
                    $materiaisContainer.hide();
                    // Limpar validação dos campos de itens quando cancelado
                    $tabelaItensBody.find('select[name="produto_id[]"]').removeAttr('required');
                } else {
                    $secaoItens.show();
                    $materiaisContainer.show();
                    // Restaurar validação dos campos de itens quando não cancelado
                    $tabelaItensBody.find('select[name="produto_id[]"]').attr('required', 'required');
                }
            }

            // Event listener para mudança de status
            $statusSelect.on('change', controlarVisibilidadeItens);

            // Atualizar estados iniciais
            controlarVisibilidadeItens();
            atualizarDesconto();

            // Forçar recálculo inicial para exibir resumo correto quando há itens pré-existentes
            setTimeout(function() {
                recalcularTotais();

                // Se há valores pré-existentes nos campos, renderizar resumo imediatamente
                if (temValorLimpo || temValorSujo) {
                    renderResumoAtual();
                }
            }, 100);
        });
    </script>
@endsection
