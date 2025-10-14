@extends('layouts.master', ['titulo' => 'Baus', 'subtitulo' => 'Catálogo de Baus Cadastrados'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($baus->id))
                            <h3>Cadastrar Bau</h3>
                        @else
                            <h3>Editar Bau</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light" href="{{ route('administracao.estoque.baus.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="bausForm" action="{{ route('administracao.estoque.baus.store') }}" method="POST" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        @csrf
                        <input id="id" name="id" type="hidden" value="{{ old('id', $baus->id) }}">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome" class="form-control @error('nome') is-invalid @enderror" value="{{ old('nome', $baus->nome) }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                            @error('nome')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="ativo" id="ativo" class="form-control select2 @error('ativo') is-invalid @enderror">
                                <option value="">Selecione</option>
                                <option value="1" @selected(old('ativo', (string) $baus->ativo) === '1')>SIM</option>
                                <option value="0" @selected(old('ativo', (string) $baus->ativo) === '0')>NÃO</option>
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
                            @can('acesso', 'administracao.estoque.baus.store')
                                <button type="submit" id="save-btn" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"><i class="ti-save"></i>Salvar</button>
                            @endcan
                            <a id="cancel-btn" class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light" href="{{ route('administracao.estoque.baus.edit', [$baus->id]) }}"><i class="ti-close"></i></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    {{-- Validação no servidor via FormRequest exibe erros acima --}}
@endsection
