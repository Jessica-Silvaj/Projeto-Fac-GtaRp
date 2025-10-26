@php
    $isNovo = $isNovo ?? empty($fila->id);
    $tituloPagina = $isNovo ? 'Cadastrar Pedido' : 'Editar Pedido';
    $subtituloPagina = $isNovo ? 'Novo registro na fila de vendas' : 'Atualizar fila de vendas';
    $formAction = $isNovo ? route('venda.fila.store') : route('venda.fila.update', $fila->id);
    $voltarRoute = route('venda.fila.index');
@endphp
@extends('layouts.master', ['titulo' => $tituloPagina, 'subtitulo' => $subtituloPagina])

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>{{ $isNovo ? 'Cadastrar Pedido' : 'Editar Pedido #' . $fila->id }}</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        <a class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light" href="{{ $voltarRoute }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" method="POST" action="{{ $formAction }}">
                @csrf
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="organizacao_id" id="organizacao_id"
                                class="form-control select2 @error('organizacao_id') is-invalid @enderror">
                                <option value="">Selecione</option>
                                @foreach ($organizacoes as $organizacao)
                                    <option value="{{ $organizacao->id }}"
                                        {{ old('organizacao_id', $fila->organizacao_id) == $organizacao->id ? 'selected' : '' }}>
                                        {{ $organizacao->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Organizacao</label>
                            @error('organizacao_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome"
                                class="form-control @error('nome') is-invalid @enderror"
                                value="{{ old('nome', $fila->nome) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Pessoa / Contato</label>
                            @error('nome')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="data_pedido" name="data_pedido"
                                class="form-control data-mask @error('data_pedido') is-invalid @enderror"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_pedido', optional($fila->data_pedido)->format('d/m/Y')) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data do Pedido</label>
                            @error('data_pedido')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="data_entrega_estimada" name="data_entrega_estimada"
                                class="form-control data-mask @error('data_entrega_estimada') is-invalid @enderror"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_entrega_estimada', optional($fila->data_entrega_estimada)->format('d/m/Y')) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data entrega estimada</label>
                            @error('data_entrega_estimada')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="usuario_id" id="usuario_id"
                                class="form-control select2 @error('usuario_id') is-invalid @enderror">
                                <option value="">Selecione</option>
                                @foreach ($responsaveis as $responsavel)
                                    <option value="{{ $responsavel->id }}"
                                        {{ old('usuario_id', $fila->usuario_id) == $responsavel->id ? 'selected' : '' }}>
                                        {{ $responsavel->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Responsavel</label>
                            @error('usuario_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="status" id="status"
                                class="form-control select2 @error('status') is-invalid @enderror">
                                @foreach ($statusLabels as $valor => $info)
                                    <option value="{{ $valor }}"
                                        {{ old('status', $fila->status) === $valor ? 'selected' : '' }}>
                                        {{ $info['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label class="float-label">Status</label>
                            @error('status')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-12">
                            <textarea name="pedido" id="pedido" rows="4" class="form-control @error('pedido') is-invalid @enderror">{{ old('pedido', $fila->pedido) }}</textarea>
                            <span class="form-bar"></span>
                            <label class="float-label">Descricao do Pedido</label>
                            @error('pedido')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                        <i class="ti-save"></i> Salvar
                    </button>
                    <a href="{{ $voltarRoute }}" class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light">
                        <i class="ti-close"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
