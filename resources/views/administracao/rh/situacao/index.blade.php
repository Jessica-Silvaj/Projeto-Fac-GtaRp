@extends('layouts.master', ['titulo' => 'Situação', 'subtitulo' => 'Situações Cadastradas'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.rh.situacao.index') }}" method="GET" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome" class="form-control"
                                value="{{ request()->get('nome') }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-6">
                            <select name="ativo" id="ativo" class="form-control">
                                <option value="">Selecione</option>
                                <option value="1" {{ request()->get('ativo') == '1' ? 'selected' : '' }}>SIM</option>
                                <option value="0" {{ request()->get('ativo') == '0' ? 'selected' : '' }}>NÃO</option>
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
                                href="{{ route('administracao.rh.situacao.index') }}">
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
                        <h3>Listagem de Situação</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        @can('acesso', 'administracao.rh.situacao.store')
                            <a id="cancel-btn" class="btn btn-primary btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.rh.situacao.edit') }}">
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
                                <th class="text-left col-md-5">Nome</th>
                                <th class="text-center col-md-5">Ativo</th>
                                <th class="text-center col-md-2">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listSituacao as $idx)
                                <tr>
                                    <td class="text-left col-md-">{{ $idx->nome }}</td>
                                    <td class="text-center col-md-5">
                                        @if ($idx->ativo == 1)
                                            <span class="pcoded-badge label label-success">SIM</span>
                                        @else
                                            <span class="pcoded-badge label label-danger">NÃO</span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-2">
                                        <div class="row">
                                            <div class="col-md-5 text-center">
                                                @can('acesso', 'administracao.rh.situacao.edit')
                                                <a type="button" class="btn btn-primary" title="Editar situação"
                                                    href="{{ route('administracao.rh.situacao.edit', [$idx->id]) }}">
                                                    <i class="ti-pencil"></i>Editar
                                                </a>
                                                @endcan
                                            </div>
                                            <div class="col-md-5 text-center">
                                                @can('acesso', 'administracao.rh.situacao.destroy')
                                                <a type="button" class="btn btn-danger" title="Excluir Situação"
                                                    onclick="mostrarConfirmacaoExclusao('Excluir Situação', 'Deseja realmente excluir a Situação {{ "\"" . $idx->nome . "\"" }}?', 'Excluir', 'Cancelar', {{ $idx->id }})">
                                                    <i class="ti-trash"></i> Excluir
                                                </a>
                                                <form id="{{ $idx->id }}"
                                                    action="{{ route('administracao.rh.situacao.destroy', [$idx->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        Registros não encontrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="3">
                                Total de Registros: <strong>{{ $listSituacao->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listSituacao->links('pagination::bootstrap-4') }}
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
