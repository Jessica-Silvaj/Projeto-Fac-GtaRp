@extends('layouts.master', ['titulo' => 'Perfil', 'subtitulo' => 'Catálogo de Perfils Cadastrados'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($perfil->id))
                            <h3>Cadastrar Perfil</h3>
                        @else
                            <h3>Editar Perfil</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button"
                            class="btn  btn-sm btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('administracao.rh.perfil.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="bausForm" action="{{ route('administracao.rh.perfil.store') }}" method="POST"
                role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        @csrf
                        <input id="id" name="id" type="hidden" value="{{ $perfil->id }}">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ $perfil->nome }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="ativo" id="ativo" class="form-control">
                                <option value="">Selecione</option>
                                <option value="1" {{ $perfil->ativo == '1' ? 'selected' : '' }}>SIM</option>
                                <option value="0" {{ $perfil->ativo == '0' ? 'selected' : '' }}>NÃO</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            @can('acesso', 'administracao.rh.perfil.store')
                                <button type="submit" id="save-btn"
                                    class="btn   btn-sm btn-primary btn-out-dashed waves-effect waves-light"><i
                                        class="ti-save"></i>Salvar</button>
                            @endcan
                            <a id="cancel-btn" class="btn  btn-sm btn-danger btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.perfil.edit', [$perfil->id]) }}"><i
                                    class="ti-close"></i></i>Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            const rules = {
                nome: {
                    required: true,
                },
                ativo: {
                    required: true,
                },
            };

            const messages = {
                nome: {
                    required: "Informe o nome.",
                },
                ativo: {
                    required: "Informe o ativo.",
                },
            };

            const form = $("#bausForm");
            validarFormulario(form, rules, messages);

        });
    </script>
@endsection
