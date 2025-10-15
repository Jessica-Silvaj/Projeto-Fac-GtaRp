@extends('layouts.master', ['titulo' => 'Configuracao de Anomalias', 'subtitulo' => 'Ajuste dos limites e regras de alerta'])

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Parametros gerais</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.sistema.configuracao.anomalia.update') }}" method="POST">
                @csrf
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="limite_percentual_bau">Percentual de ocupacao critica do bau</label>
                            <input type="text" name="limite_percentual_bau" id="limite_percentual_bau" class="form-control"
                                value="{{ old('limite_percentual_bau', $config['limite_percentual_bau'] ?? 0.8) }}">
                            <small class="form-text text-muted">Valor entre 0 e 1 (ex.: 0.8 = 80%)</small>
                            @error('limite_percentual_bau')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="limite_padrao_bau">Capacidade padrao do bau</label>
                            <input type="text" name="limite_padrao_bau" id="limite_padrao_bau" class="form-control"
                                value="{{ old('limite_padrao_bau', $config['limite_padrao_bau'] ?? 1000) }}">
                            <small class="form-text text-muted">Usado quando o bau nao tiver limite especifico.</small>
                            @error('limite_padrao_bau')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="limite_quantidade_movimento">Quantidade limite para movimentos atipicos</label>
                            <input type="text" name="limite_quantidade_movimento" id="limite_quantidade_movimento" class="form-control"
                                value="{{ old('limite_quantidade_movimento', $config['limite_quantidade_movimento'] ?? 500) }}">
                            @error('limite_quantidade_movimento')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="janela_movimento_dias">Janela (dias) para detectar movimentos</label>
                            <input type="text" name="janela_movimento_dias" id="janela_movimento_dias" class="form-control"
                                value="{{ old('janela_movimento_dias', $config['janela_movimento_dias'] ?? 7) }}">
                            @error('janela_movimento_dias')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="limite_estoque_critico">Estoque critico (ate)</label>
                            <input type="text" name="limite_estoque_critico" id="limite_estoque_critico" class="form-control"
                                value="{{ old('limite_estoque_critico', $config['limite_estoque_critico'] ?? 10) }}">
                            @error('limite_estoque_critico')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-4">
                            <label for="limite_estoque_baixo">Estoque baixo (alerta rapido)</label>
                            <input type="text" name="limite_estoque_baixo" id="limite_estoque_baixo" class="form-control"
                                value="{{ old('limite_estoque_baixo', $config['limite_estoque_baixo'] ?? 5) }}">
                            @error('limite_estoque_baixo')
                                <span class="text-danger small d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-header">
                    <h3>Limites especificos por bau e item</h3>
                    <small class="text-muted">Defina limites especificos para itens em baus especificos (sobrepoe o limite critico global).</small>
                </div>
                <div class="card-block">
                    <div id="limites-container">
                        @php
                            $oldBaus = old('limites_especificos.bau_id', []);
                            $oldItens = old('limites_especificos.item_id', []);
                            $oldLimites = old('limites_especificos.limite', []);
                            $linhas = count($oldBaus) ? count($oldBaus) : max(count($limitesEspecificos ?? []), 1);
                        @endphp
                        @for ($i = 0; $i < $linhas; $i++)
                            @php
                                $linhaAtual = $limitesEspecificos[$i] ?? [];
                                $bauId = $oldBaus[$i] ?? ($linhaAtual['bau_id'] ?? null);
                                $itemId = $oldItens[$i] ?? ($linhaAtual['item_id'] ?? null);
                                $limite = $oldLimites[$i] ?? ($linhaAtual['limite'] ?? '');
                                $bauNome = $linhaAtual['bau_nome'] ?? null;
                                $itemNome = $linhaAtual['item_nome'] ?? null;
                            @endphp
                            <div class="form-row align-items-end limite-item mb-2">
                                <div class="form-group col-md-4">
                                    <label>Bau</label>
                                    <select name="limites_especificos[bau_id][]" class="form-control select2 select-bau"
                                        data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}" data-placeholder="Selecione o bau">
                                        <option value=""></option>
                                        @if ($bauId)
                                            <option value="{{ $bauId }}" selected>{{ $bauNome ?? 'Bau #' . $bauId }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Item</label>
                                    <select name="limites_especificos[item_id][]" class="form-control select2 select-item"
                                        data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}"
                                        data-placeholder="Selecione o item">
                                        <option value=""></option>
                                        @if ($itemId)
                                            <option value="{{ $itemId }}" selected>{{ $itemNome ?? 'Item #' . $itemId }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Limite</label>
                                    <input type="text" name="limites_especificos[limite][]" class="form-control"
                                        value="{{ $limite }}">
                                </div>
                                <div class="form-group col-md-1 text-right">
                                    <button type="button" class="btn btn-sm btn-danger btn-remove-limite">Remover</button>
                                </div>
                            </div>
                        @endfor
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-limite">
                        Adicionar limite especifico
                    </button>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                        <i class="ti-save"></i> Salvar configuracoes
                    </button>
                    <a href="{{ route('bau.lancamentos.anomalias') }}"
                        class="btn btn-sm btn-secondary btn-out-dashed waves-effect waves-light ml-2">
                        Voltar ao dashboard
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function() {
            function initSelect2(scope) {
                if (typeof $ === 'undefined' || !$.fn.select2) {
                    return;
                }
                $(scope).find('.select2').each(function() {
                    var $el = $(this);
                    if ($el.data('select2')) {
                        $el.select2('destroy');
                    }
                    var ajaxUrl = $el.data('ajax-url');
                    $el.select2({
                        theme: 'bootstrap',
                        placeholder: $el.data('placeholder') || '',
                        allowClear: true,
                        ajax: ajaxUrl ? {
                            url: ajaxUrl,
                            dataType: 'json',
                            delay: 300,
                            data: function(params) {
                                return {
                                    q: params.term
                                };
                            },
                            processResults: function(data) {
                                return data;
                            }
                        } : undefined
                    });
                });
            }

            var container = document.getElementById('limites-container');
            var addBtn = document.getElementById('btn-add-limite');
            if (addBtn && container) {
                addBtn.addEventListener('click', function() {
                    var div = document.createElement('div');
                    div.className = 'form-row align-items-end limite-item mb-2';
                    div.innerHTML =
                        '<div class="form-group col-md-4">' +
                        '<label>Bau</label>' +
                        '<select name="limites_especificos[bau_id][]" class="form-control select2 select-bau" data-ajax-url="{{ route('bau.lancamentos.bau.baus.search') }}" data-placeholder="Selecione o bau"><option value=""></option></select>' +
                        '</div>' +
                        '<div class="form-group col-md-4">' +
                        '<label>Item</label>' +
                        '<select name="limites_especificos[item_id][]" class="form-control select2 select-item" data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}" data-placeholder="Selecione o item"><option value=""></option></select>' +
                        '</div>' +
                        '<div class="form-group col-md-3">' +
                        '<label>Limite</label>' +
                        '<input type="text" name="limites_especificos[limite][]" class="form-control">' +
                        '</div>' +
                        '<div class="form-group col-md-1 text-right">' +
                        '<button type="button" class="btn btn-sm btn-danger btn-remove-limite">Remover</button>' +
                        '</div>';
                    container.appendChild(div);
                    initSelect2(div);
                });
                container.addEventListener('click', function(e) {
                    if (e.target && e.target.classList.contains('btn-remove-limite')) {
                        var item = e.target.closest('.limite-item');
                        if (item) {
                            item.remove();
                        }
                    }
                });
                initSelect2(container);
            }
        })();
    </script>
@endsection

