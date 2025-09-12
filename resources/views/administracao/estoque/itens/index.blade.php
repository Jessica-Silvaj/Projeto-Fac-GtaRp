@extends('layouts.master', ['titulo' => 'Itens', 'subtitulo' => 'Materiais Cadastrados'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('administracao.estoque.itens.index') }}" method="GET" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-items-center">
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
                                href="{{ route('administracao.estoque.itens.index') }}">
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
                        <h3>Listagem de Itens</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="cancel-btn" class="btn btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('administracao.estoque.itens.index') }}">
                            <i class="ti-plus"></i> Novo
                        </a>
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
                            @forelse ($listItens as $idx )
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
                                                <button type="button" class="btn btn-primary" title="Editar Itens" href="">
                                                    <i class="ti-pencil"></i>Editar
                                                </button>
                                            </div>
                                            <div class="col-md-5 text-center">
                                                <button type="button" class="btn btn-danger" title="Excluir Itens" href="">
                                                    <i class="ti-trash"></i> Excluir
                                                </button>
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
                                Total de Registros: <strong>{{ $listItens->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listItens->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endsection
@section('script')
@endsection
