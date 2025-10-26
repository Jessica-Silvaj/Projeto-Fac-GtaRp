@extends('layouts.master', ['titulo' => 'Organizacao', 'subtitulo' => 'Catalogo de Organizacoes'])
@section('conteudo')
    @php
        $organizacao = $organizacao ?? null;
        $isEdit = !empty(optional($organizacao)->id);
    @endphp
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>{{ $isEdit ? 'Editar Organizacao' : 'Cadastrar Organizacao' }}</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button"
                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('administracao.fabricacao.organizacao.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="organizacaoForm"
                action="{{ route('administracao.fabricacao.organizacao.store') }}" method="POST" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        @csrf
                        <input id="id" name="id" type="hidden" value="{{ old('id', optional($organizacao)->id) }}">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome"
                                class="form-control @error('nome') is-invalid @enderror"
                                value="{{ old('nome', optional($organizacao)->nome) }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                            @error('nome')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="ativo" id="ativo"
                                class="form-control select2 @error('ativo') is-invalid @enderror">
                                <option value="">Selecione</option>
                                <option value="1" @selected(old('ativo', (string) optional($organizacao)->ativo) === '1')>SIM</option>
                                <option value="0" @selected(old('ativo', (string) optional($organizacao)->ativo) === '0')>NAO</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                            @error('ativo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            @can('acesso', 'administracao.fabricacao.organizacao.store')
                                <button type="submit" id="save-btn"
                                    class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light">
                                    <i class="ti-save"></i>Salvar
                                </button>
                            @endcan
                            <a id="cancel-btn"
                                class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.fabricacao.organizacao.index') }}">
                                <i class="ti-close"></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    {{-- Validacao feita via FormRequest --}}
@endsection
