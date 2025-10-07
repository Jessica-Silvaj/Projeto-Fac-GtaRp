@extends('layouts.master', ['titulo' => 'Usuário', 'subtitulo' => 'Usuários Cadastrados'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.rh.usuario.index') }}" method="GET" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ request()->get('nome') }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="situacao" id="situacao" class="form-control">
                                <option value="">Selecione</option>
                                @foreach ($situacao as $idx)
                                    <option value="{{ $idx->id }}"
                                        {{ request()->get('situacao') == $idx->id ? 'selected' : '' }}> {{ $idx->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="situacao" class="float-label">Situação</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="perfil" id="perfil" class="form-control">
                                <option value="">Selecione</option>
                                @foreach ($perfil as $idx)
                                    <option value="{{ $idx->id }}"
                                        {{ request()->get('perfil') == $idx->id ? 'selected' : '' }}> {{ $idx->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="perfil" class="float-label">Perfil</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="funcoes" id="funcoes" class="form-control">
                                <option value="">Selecione</option>
                                @foreach ($funcoes as $idx)
                                    <option value="{{ $idx->id }}"
                                        {{ request()->get('funcoes') == $idx->id ? 'selected' : '' }}> {{ $idx->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="funcoes" class="float-label">Função</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" id="save-btn"
                                class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Pesquisar
                            </button>
                            <a id="cancel-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.usuario.index') }}">
                                <i class="ti-close"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        <h3>Listagem de Usuário</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        @can('acesso', 'administracao.rh.usuario.store')
                            <a id="cancel-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.usuario.edit') }}">
                                <i class="ti-plus"></i> Novo
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-left col-md-3">Nome</th>
                                <th class="text-center col-md-2">Situação</th>
                                <th class="text-center col-md-2">Perfil</th>
                                <th class="text-center col-md-3">Funções</th>
                                <th class="text-center col-md-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listUsuario as $idx)
                                <tr>
                                    <td class="text-left col-md-3">{{ $idx->matricula }} - {{ $idx->nome }}</td>
                                    <td class="text-center col-md-2">{{ $idx->situacao->nome }}</td>
                                    <td class="text-center col-md-2">{{ $idx->perfil->nome }}</td>
                                    <td class="text-center col-md-3">
                                        @forelse ($idx->funcoes as $f)
                                            <span class="badge badge-success">{{ $f->nome }}</span>
                                        @empty
                                            <span class="text-muted">SEM FUNÇÕES</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center col-md-2">
                                        <div class="text-center table-actions">
                                            @can('acesso', 'administracao.rh.usuario.edit')
                                                <a type="button" class="btn btn-sm btn-primary" title="Editar Usuário"
                                                    href="{{ route('administracao.rh.usuario.edit', [$idx->id]) }}">
                                                    <i class="ti-pencil"></i>Editar
                                                </a>
                                            @endcan
                                            @can('acesso', 'administracao.rh.usuario.destroy')
                                                <a type="button" class="btn btn-sm btn-danger" title="Excluir Usuário"
                                                    onclick="mostrarConfirmacaoExclusao('Excluir Usuário', 'Deseja realmente excluir o usuário {{ "\"" . $idx->nome . "\"" }}?', 'Excluir', 'Cancelar', {{ $idx->id }})">
                                                    <i class="ti-trash"></i> Excluir
                                                </a>
                                                <form id="{{ $idx->id }}"
                                                    action="{{ route('administracao.rh.usuario.destroy', [$idx->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        Registros não encontrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="5">
                                Total de Registros: <strong>{{ $listUsuario->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listUsuario->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function mostrarConfirmacaoExclusao(titulo, texto, btn1, btn2, valor) {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger'
                },
                buttonStyling: false
            })

            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButton: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: btn1,
                cancelButtonText: btn2,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#' + valor).submit();
                }
            })
        }
    </script>
@endsection
