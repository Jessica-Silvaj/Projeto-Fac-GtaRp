@extends('layouts.master', ['titulo' => 'Usuário', 'subtitulo' => 'Usuários Cadastrados'])
<style>
    #lista_funcoes {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem 1rem;
    }

    /* espaçamento linha x coluna */
    #lista_funcoes .form-check {
        margin: 0;
    }

    /* zera margens do item (o gap cuida do espaço) */
</style>

@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($usuario->id))
                            <h3>Cadastrar Usuário</h3>
                        @else
                            <h3>Editar Usuário</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button" class="btn btn-sm btn-primary btn-out-dashed waves-effec waves-light"
                            href="{{ route('administracao.rh.usuario.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="bausForm" action="{{ route('administracao.rh.usuario.store') }}" method="POST"
                role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        @csrf
                        <input id="id" name="id" type="hidden" value="{{ $usuario->id }}">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="matricula" name="matricula" class="form-control"
                                value="{{ $usuario->matricula }}">
                            <span class="form-bar"></span>
                            <label for="matricula" class="float-label">Passaporte</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ $usuario->nome }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="password" id="senha" name="senha" class="form-control"
                                autocomplete="new-password" value="">
                            <span class="form-bar"></span>
                            <label for="senha" class="float-label">Senha</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="situacao" id="situacao" class="form-control">
                                <option value="">Selecione</option>
                                @foreach ($situacao as $idx)
                                    <option value="{{ $idx->id }}"
                                        {{ $usuario->situacao_id == $idx->id ? 'selected' : '' }}>{{ $idx->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="situacao" class="float-label">Situação</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="perfil" id="perfil" class="form-control">
                                <option value="">Selecione</option>
                                @foreach ($perfil as $idx)
                                    <option value="{{ $idx->id }}"
                                        {{ $usuario->perfil_id == $idx->id ? 'selected' : '' }}>{{ $idx->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="perfil" class="float-label">Perfil</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_admissao" name="data_admissao" class="form-control data-mask"
                                value="{{ empty($usuario->data_admissao) ? '' : date('d/m/Y', strtotime($usuario->data_admissao)) }}"
                                data-format="DD/MM/YYYY">
                            <span class="form-bar"></span>
                            <label for="data_admissao" class="float-label">Data de Admissão</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-12">
                            <label class="d-block mb-2" style="color: #d59725;font-size: 11px;">Funções</label>

                            <div id="lista_funcoes" class="d-flex flex-wrap">
                                @foreach ($funcoes as $f)
                                    <div class="form-check form-check-inline mr-3 mb-2">
                                        <input type="checkbox" class="form-check-input funcao-item"
                                            id="funcao_{{ $f->id }}" name="funcoes[]" value="{{ $f->id }}"
                                            {{ in_array($f->id, $selecionadas, true) ? 'checked' : '' }}>
                                        <label class="form-check-label ml-1" style="color: #d59725;font-size: 12px;"
                                            for="funcao_{{ $f->id }}">
                                            {{ $f->nome }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            @can('acesso', 'administracao.rh.usuario.store')
                                <button type="submit" id="save-btn"
                                    class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"><i
                                        class="ti-save"></i>Salvar</button>
                            @endcan
                            <a id="cancel-btn" class="btn btn-danger btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.usuario.edit', [$usuario->id]) }}"><i
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
                matricula: {
                    required: true,
                },
                nome: {
                    required: true,
                },
                situacao: {
                    required: true,
                },
                perfil: {
                    required: true,
                },
                data_admissao: {
                    required: true,
                }
            };

            const messages = {
                matricula: {
                    required: "Informe o passaporte.",
                },
                nome: {
                    required: "Informe o nome.",
                },
                situacao: {
                    required: "Informe a situação.",
                },
                perfil: {
                    required: "Informe o perfil.",
                },
                data_admissao: {
                    required: "Informe a data de admissão.",
                },
            };

            const form = $("#bausForm");
            validarFormulario(form, rules, messages);

        });
    </script>
@endsection
