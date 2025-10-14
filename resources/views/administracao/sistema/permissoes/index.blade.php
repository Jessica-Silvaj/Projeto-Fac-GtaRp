@extends('layouts.master', ['titulo' => 'Permissões', 'subtitulo' => 'Configuração de Permissões'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.sistema.permissoes.index') }}" method="GET"
                role="form">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ request('nome') }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="descricao" name="descricao" class="form-control"
                                value="{{ request('descricao') }}">
                            <span class="form-bar"></span>
                            <label for="descricao" class="float-label">Descrição</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="ativo" id="ativo" class="form-control select2">
                                <option value="">Selecione</option>
                                <option value="1" @selected(request('ativo') === '1')>SIM</option>
                                <option value="0" @selected(request('ativo') === '0')>NÃO</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="funcao" id="funcao" class="form-control select2">
                                <option value="">Selecione</option>
                                @foreach ($funcoes as $f)
                                    <option value="{{ $f->id }}" @selected(request('funcao') == $f->id)>{{ $f->nome }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="form-bar"></span>
                            <label for="funcao" class="float-label">Função</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center text-start mt-2 mt-md-0">
                            <button type="submit" id="save-btn"
                                class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Pesquisar
                            </button>
                            <a id="cancel-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.sistema.permissoes.index') }}">
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
                    <div class="col-12 col-md-10">
                        <h3>Listagem de Permissões</h3>
                    </div>
                    <div class="col-12 col-md-2 text-md-right text-start mt-2 mt-md-0">
                        @can('acesso', 'administracao.sistema.permissoes.store')
                            <a id="novo-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.sistema.permissoes.edit') }}">
                                <i class="ti-plus"></i> Nova
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
                                <th class="text-left col-md-3">Descrição</th>
                                <th class="text-center col-md-3">Funções</th>
                                <th class="text-center col-md-1">Ativo</th>
                                <th class="text-center col-md-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listPermissoes as $idx)
                                <tr>
                                    <td class="text-left col-md-3">{{ $idx->nome }}</td>
                                    <td class="text-left col-md-3">{{ $idx->descricao }}</td>
                                    <td class="text-center col-md-3">
                                        @forelse ($idx->funcoes as $f)
                                            <span class="pcoded-badge label label-success"
                                                style="margin-right: 4px;">{{ $f->nome }}</span>
                                        @empty
                                            <span class="text-muted">SEM FUNÇÕES</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center col-md-1">
                                        @if ($idx->ativo == 1)
                                            <span class="pcoded-badge label label-success">SIM</span>
                                        @else
                                            <span class="pcoded-badge label label-danger">NÃO</span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-2">
                                        <div class="text-center table-actions">
                                            @can('acesso', 'administracao.sistema.permissoes.edit')
                                                <a type="button" class="btn btn-primary btn-sm" title="Editar permissão"
                                                    href="{{ route('administracao.sistema.permissoes.edit', [$idx->id]) }}">
                                                    <i class="ti-pencil"></i> Editar
                                                </a>
                                            @endcan
                                            @can('acesso', 'administracao.sistema.permissoes.destroy')
                                                <a type="button" class="btn btn-danger btn-sm" title="Excluir permissão"
                                                    onclick="mostrarConfirmacaoExclusao('Excluir permissão', 'Deseja realmente excluir a permissão {{ "\"" . $idx->nome . "\"" }}?', 'Excluir', 'Cancelar', {{ $idx->id }})">
                                                    <i class="ti-trash"></i> Excluir
                                                </a>
                                                <form id="{{ $idx->id }}"
                                                    action="{{ route('administracao.sistema.permissoes.destroy', [$idx->id]) }}"
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
                                    <td colspan="5">Nenhum registro encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="5">Total de registros:
                                <strong>{{ method_exists($listPermissoes, 'total') ? $listPermissoes->total() : $listPermissoes->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listPermissoes->links('pagination::bootstrap-4') }}
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
