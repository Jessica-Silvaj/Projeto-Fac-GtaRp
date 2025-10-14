@extends('layouts.master', ['titulo' => 'Permissões', 'subtitulo' => 'Configurações de Permissão'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($permissoes->id))
                            <h3>Nova Permissão</h3>
                        @else
                            <h3>Editar Permissão</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light" href="{{ route('administracao.sistema.permissoes.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>

            <form class="form-material" id="permissoesForm" action="{{ route('administracao.sistema.permissoes.store') }}" method="POST" role="form">
                @csrf
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        <input id="id" name="id" type="hidden" value="{{ old('id', $permissoes->id) }}">

                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $permissoes->nome) }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                            @error('nome')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="descricao" name="descricao" class="form-control @error('descricao') is-invalid @enderror" value="{{ old('descricao', $permissoes->descricao) }}">
                            <span class="form-bar"></span>
                            <label for="descricao" class="float-label">Descrição</label>
                            @error('descricao')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="ativo" id="ativo" class="form-control select2 @error('ativo') is-invalid @enderror">
                                <option value="">Selecione</option>
                                <option value="1" @selected(old('ativo', (string) $permissoes->ativo) === '1')>SIM</option>
                                <option value="0" @selected(old('ativo', (string) $permissoes->ativo) === '0')>NÃO</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                            @error('ativo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group form-default form-static-label col-md-12">
                            <label class="d-block mb-2" style="color: #d59725;font-size: 11px;">Funções</label>
                            <div id="lista_funcoes" class="d-flex flex-wrap" style="gap:.5rem 1rem">
                                @foreach ($funcoes as $f)
                                    <div class="form-check form-check-inline mr-3 mb-2" style="margin:0">
                                        <input type="checkbox" class="form-check-input funcao-item" id="funcao_{{ $f->id }}" name="funcoes[]" value="{{ $f->id }}" @checked(in_array($f->id, $selecionadas, true))>
                                        <label class="form-check-label ml-1" style="color:#d59725;font-size:12px;" for="funcao_{{ $f->id }}">
                                            {{ $f->nome }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('funcoes')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            @error('funcoes.*')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            @can('acesso', 'administracao.sistema.permissoes.store')
                                <button type="submit" id="save-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light">
                                    <i class="ti-save"></i> Salvar
                                </button>
                            @endcan
                            <a id="cancel-btn" class="btn btn-danger btn-sm btn-out-dashed waves-effect waves-light" href="{{ route('administracao.sistema.permissoes.index') }}">
                                <i class="ti-close"></i> Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    {{-- Validação do lado do servidor via FormRequest já cobre erros; script opcional removido --}}
@endsection
