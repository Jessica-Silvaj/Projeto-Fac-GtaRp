@extends('layouts.master', ['titulo' => 'Perfil do usuário', 'subtitulo' => 'Perfil do usuário'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>Dados do usuário</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        <button id="edit-btn" type="button" class="btn btn-sm btn-primary waves-effect waves-light f-right">
                            <i class="ti-pencil-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
            <form class="form-material dadosPerfil" id="form-dadosPerfil" action="{{ route('perfil.store') }}" method="POST"
                autocomplete="off">
                <div class="card-block">
                    <div class="row justify-content-center">
                        @csrf
                        <input id="id" name="id" type="hidden" value="{{ $usuario->id }}">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ $usuario->nome }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="matricula" name="matricula" class="form-control date"
                                value="{{ $usuario->matricula }}">
                            <span class="form-bar"></span>
                            <label for="matricula" class="float-label">Matrícula</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="password" id="senha" name="senha" class="form-control date" value=""
                                autocomplete="new-password">
                            <span class="form-bar"></span>
                            <label for="senha" class="float-label">Senha</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_admissao" name="data_admissao" class="form-control data-mask"
                                value="{{ date('d/m/Y', strtotime($usuario->data_admissao)) }}" data-format="DD/MM/YYYY">
                            <span class="form-bar"></span>
                            <label for="data_admissao" class="float-label">Data de Admissão</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="perfil_id" id="perfil_id" class="form-control select2">
                                <option value="">Selecione</option>
                                @foreach ($perfil as $item)
                                    <option {{ $usuario->perfil_id == $item->id ? 'selected' : '' }}
                                        value="{{ $item->id }}">{{ $item->nome }}</option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="perfil_id" class="float-label">Perfil</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="situacao_id" id="situacao_id" class="form-control select2">
                                <option value="">Selecione</option>
                                @foreach ($situacao as $item)
                                    <option {{ $usuario->situacao_id == $item->id ? 'selected' : '' }}
                                        value="{{ $item->id }}">{{ $item->nome }}</option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="situacao_id" class="float-label">Situação</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" id="save-btn" class="btn btn-primary btn-out-dashed waves-effect waves-light"><i
                                    class="ti-save"></i>Salvar</button>
                            <a id="cancel-btn" class="btn btn-danger btn-out-dashed waves-effect waves-light"
                                href="{{ route('perfil.edit', $usuario->id) }}"><i class="ti-close"></i></i>Cancelar</a>
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
            // Inicialmente, desabilitar a edição
            desabilitarEdicao();

            // Adicionar um manipulador de eventos para o botão de edição
            $(' #edit-btn').on('click', function() {
                // Verificar se a edição está ativada
                if ($(this).hasClass('editing')) {
                    // Desabilitar a edição
                    desabilitarEdicao();
                } else {
                    // Habilitar a edição
                    habilitarEdicao();
                }
            });

            // Função para desabilitar a edição
            function desabilitarEdicao() {
                $('#form-dadosPerfil .form-control').prop('disabled', true);
                $('#edit-btn').removeClass('btn-danger').addClass('btn-primary').removeClass('editing');
                $('#edit-btn i').removeClass('ti-close').addClass('ti-pencil-alt');
                $('#edit-btn').removeClass('editing');
                $('#save-btn, #cancel-btn').hide();
            }

            // Função para habilitar a edição
            function habilitarEdicao() {
                $('#form-dadosPerfil .form-control').prop('disabled', false);
                $('#edit-btn').removeClass('btn-primary').addClass('btn-danger editing');
                $('#edit-btn i').removeClass('ti-pencil-alt').addClass('ti-close');
                $('#edit-btn').addClass('editing');
                $('#save-btn, #cancel-btn').show();
            }

            const rules = {
                nome: {
                    required: true,
                },
                matricula: {
                    required: true,
                },
                data_admissao: {
                    required: true,
                },
                situacao_id: {
                    required: true,
                },
                perfil_id: {
                    required: true,
                },

            };

            const messages = {
                nome: {
                    required: "Informe o nome.",
                },
                matricula: {
                    required: "Informe a matricula.",
                },
                data_admissao: {
                    required: "Informe a data de admissao.",
                },
                situacao_id: {
                    required: "Informe a situação.",
                },
                perfil_id: {
                    required: "Informe o perfil.",
                },
            };

            const form = $("#form-dadosPerfil");
            validarFormulario(form, rules, messages);

        });
    </script>
@endsection
