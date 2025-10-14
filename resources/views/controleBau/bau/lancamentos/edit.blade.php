@extends('layouts.master', ['titulo' => 'Lançamento', 'subtitulo' => 'Entradas, saídas e transferências'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($lancamento->id))
                            <h3>Novo Lançamento</h3>
                        @else
                            <h3>Editar Lançamento</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button"
                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('bau.lancamentos.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="lancamentoForm" action="{{ route('bau.lancamentos.store') }}" method="POST"
                role="">
                <div class="card-block">
                    @csrf
                    <input id="id" name="id" type="hidden" value="{{ old('id', $lancamento->id) }}">

                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="itens_id" id="itens_id"
                                class="form-control select2 @error('itens_id') is-invalid @enderror"
                                data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}"
                                placeholder="Digite para buscar…">
                                <option value=""></option>
                                @if (!empty($lancamento->itens_id))
                                    <option value="{{ $lancamento->itens_id }}" selected>
                                        {{ optional($lancamento->item)->nome }}</option>
                                @endif
                            </select>
                            <span class="form-bar"></span>
                            <label for="itens_id" class="float-label">Item</label>
                            @error('itens_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="tipo" id="tipo"
                                class="form-control select2 @error('tipo') is-invalid @enderror">
                                <option value="">Selecione</option>
                                <option value="ENTRADA" @selected(old('tipo', $lancamento->tipo) == 'ENTRADA')>ENTRADA</option>
                                <option value="SAIDA" @selected(old('tipo', $lancamento->tipo) == 'SAIDA')>SAÍDA</option>
                                <option value="TRANSFERENCIA" @selected(old('tipo', $lancamento->tipo) == 'TRANSFERENCIA')>TRANSFERÊNCIA</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="tipo" class="float-label">Tipo</label>
                            @error('tipo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" min="1" id="quantidade" name="quantidade"
                                class="form-control @error('quantidade') is-invalid @enderror"
                                value="{{ old('quantidade', $lancamento->quantidade ?? 1) }}">
                            <span class="form-bar"></span>
                            <label for="quantidade" class="float-label">Quantidade</label>
                            @error('quantidade')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-6 grupo-origem">
                            <select name="bau_origem_id" id="bau_origem_id"
                                class="form-control select2 @error('bau_origem_id') is-invalid @enderror"
                                placeholder="Selecione">
                                <option value="">Selecione</option>
                                @foreach ($bausList as $b)
                                    <option value="{{ $b->id }}" @selected(old('bau_origem_id', $lancamento->bau_origem_id) == $b->id)>{{ $b->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="bau_origem_id" class="float-label">Baú Origem</label>
                            @error('bau_origem_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6 grupo-destino">
                            <select name="bau_destino_id" id="bau_destino_id"
                                class="form-control select2 @error('bau_destino_id') is-invalid @enderror"
                                placeholder="Selecione">
                                <option value="">Selecione</option>
                                @foreach ($bausList as $b)
                                    <option value="{{ $b->id }}" @selected(old('bau_destino_id', $lancamento->bau_destino_id) == $b->id)>{{ $b->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="bau_destino_id" class="float-label">Baú Destino</label>
                            @error('bau_destino_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-12">
                            <input type="text" id="observacao" name="observacao"
                                class="form-control @error('observacao') is-invalid @enderror"
                                value="{{ old('observacao', $lancamento->observacao) }}">
                            <span class="form-bar"></span>
                            <label for="observacao" class="float-label">Observação</label>
                            @error('observacao')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" id="save-btn"
                                class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"><i
                                    class="ti-save"></i>Salvar</button>
                            <a id="cancel-btn" class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light"
                                href="{{ route('bau.lancamentos.edit', [$lancamento->id]) }}"><i
                                    class="ti-close"></i>Cancelar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function atualizarVisibilidadeTipo() {
            var tipo = $('#tipo').val();
            var $origem = $('.grupo-origem');
            var $destino = $('.grupo-destino');

            // reset col sizes before applying
            $origem.removeClass('col-md-12 col-md-6');
            $destino.removeClass('col-md-12 col-md-6');

            if (tipo === 'ENTRADA') {
                // Exibe apenas destino, ocupa linha toda
                $origem.hide();
                $destino.show().addClass('col-md-12');
                $('#bau_origem_id').val(null).trigger('change');
            } else if (tipo === 'SAIDA') {
                // Exibe apenas origem, ocupa linha toda
                $origem.show().addClass('col-md-12');
                $destino.hide();
                $('#bau_destino_id').val(null).trigger('change');
            } else if (tipo === 'TRANSFERENCIA') {
                // Exibe ambos, meio a meio
                $origem.show().addClass('col-md-6');
                $destino.show().addClass('col-md-6');
            } else {
                // Nenhum selecionado: oculta ambos
                $origem.hide();
                $destino.hide();
            }
        }
        $(function() {
            atualizarVisibilidadeTipo();
            $('#tipo').on('change', atualizarVisibilidadeTipo);
        });
    </script>
@endsection

