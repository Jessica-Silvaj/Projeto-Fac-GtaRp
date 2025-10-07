@extends('layouts.master', ['titulo' => 'permissãoƒÂµes', 'subtitulo' => 'ConfiguraÃƒÂ§ÃƒÂµes de permissão£o'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.sistema.permissãoes.index') }}" method="GET"
                role="form">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ request('nome') }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="descricao" name="descricao" class="form-control"
                                value="{{ request('descricao') }}">
                            <span class="form-bar"></span>
                            <label for="descricao" class="float-label">DescriÃƒÂ§ÃƒÂ£o</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="ativo" id="ativo" class="form-control">
                                <option value="">Selecione</option>
                                <option value="1" @selected(request('ativo') === '1')>SIM</option>
                                <option value="0" @selected(request('ativo') === '0')>NÃƒÆ’O</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" id="save-btn"
                                class="btn btn-success btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Pesquisar
                            </button>
                            <a id="cancel-btn" class="btn btn-primary btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.sistema.permissãoes.index') }}">
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
                        <h3>Listagem de permissãoƒÂµes</h3>
                    </div>
                    <div class="col-12 col-md-2 text-md-right text-start mt-2 mt-md-0">
                        @can('acesso', 'administracao.sistema.permissãoes.store')
                            <a id="novo-btn" class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.sistema.permissãoes.edit') }}">
                                <i class="ti-plus"></i> Nova permissão£o
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
                                <th class="text-left col-md-3">DescriÃƒÂ§ÃƒÂ£o</th>
                                <th class="text-center col-md-3">Ativo</th>
                                <th class="text-center col-md-3">AÃƒÂ§ÃƒÂµes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listpermissãoes as $idx)
                                <tr>
                                    <td class="text-left col-md-3">{{ $idx->nome }}</td>
                                    <td class="text-left col-md-3">{{ $idx->descricao }}</td>
                                    <td class="text-center col-md-3">
                                        @if ($idx->ativo == 1)
                                            <span class="pcoded-badge label label-success">SIM</span>
                                        @else
                                            <span class="pcoded-badge label label-danger">NÃƒÆ’O</span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-3">
                                        <div class="text-center table-actions">
                                            @can('acesso', 'administracao.sistema.permissãoes.edit')
                                                <a type="button" class="btn btn-primary btn-sm" title="Editar permissãoo£o"
                                                    href="{{ route('administracao.sistema.permissãoes.edit', [$idx->id]) }}">
                                                    <i class="ti-pencil"></i> Editar
                                                </a>
                                            @endcan
                                            @can('acesso', 'administracao.sistema.permissãoes.destroy')
                                                <a type="button" class="btn btn-danger btn-sm" title="Excluir permissãoo£o"
                                                    onclick="mostrarConfirmacaoExclusao('Excluir permissãoo£o', 'Deseja realmente excluir a permissão£o {{ "\"" . $idx->nome . "\"" }}?', 'Excluir', 'Cancelar', {{ $idx->id }})">
                                                    <i class="ti-trash"></i> Excluir
                                                </a>
                                                <form id="{{ $idx->id }}"
                                                    action="{{ route('administracao.sistema.permissãoes.destroy', [$idx->id]) }}"
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
                                    <td colspan="4">Nenhum registro encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="4">Total de registros: <strong>{{ $listpermissãoes->count() }}</strong></td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listpermissãoes->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        function mostrarConfirmacaoExclusao(titulo, texto, btn1, btn2, valor) {
            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: btn1,
                cancelButtonText: btn2,
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(String(valor)).submit();
                }
            });
        }
    </script>
@endsection
