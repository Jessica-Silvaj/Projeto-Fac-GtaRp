@extends('layouts.master', ['titulo' => 'Lançamentos', 'subtitulo' => 'Entradas, saídas e transferências'])
@php
    use Illuminate\Support\Str;
@endphp
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros</h3>
            </div>
            <form class="form-material" action="{{ route('bau.lancamentos.index') }}" method="GET" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-items-center">
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="tipo" id="tipo" class="form-control select2" placeholder="Selecione">
                                <option value="">Selecione</option>
                                <option value="ENTRADA" {{ request('tipo') == 'ENTRADA' ? 'selected' : '' }}>ENTRADA
                                </option>
                                <option value="SAIDA" {{ request('tipo') == 'SAIDA' ? 'selected' : '' }}>SAÍDA</option>
                                <option value="TRANSFERENCIA" {{ request('tipo') == 'TRANSFERENCIA' ? 'selected' : '' }}>
                                    TRANSFERÊNCIA</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="tipo" class="float-label">Tipo</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-8">
                            <select name="itens_id" id="itens_id" class="form-control select2"
                                data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}"
                                placeholder="Digite para buscar…">
                                <option value=""></option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="itens_id" class="float-label">Item</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" id="save-btn"
                                class="btn btn-sm btn-success btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Pesquisar
                            </button>
                            <a id="cancel-btn" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                                href="{{ route('bau.lancamentos.index') }}">
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
                        <h3>Listagem de Lançamentos</h3>
                    </div>
                    <div class="col-md-2 text-right">
                        @can('acesso', 'bau.lancamentos.store')
                            <a id="cancel-btn" class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                                href="{{ route('bau.lancamentos.edit') }}">
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
                                <th class="text-left">Data</th>
                                <th class="text-left">Item</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Quantidade</th>
                                <th class="text-left">Origem</th>
                                <th class="text-left">Destino</th>
                                <th class="text-left">Usuário</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listLancamentos as $idx)
                                @php
                                    $fabricacaoAuto = Str::startsWith(
                                        Str::upper((string) ($idx->observacao ?? '')),
                                        'FABRICACÃO AUTOMATICA',
                                    );
                                @endphp
                                <tr>
                                    <td class="text-left">
                                        {{ \Carbon\Carbon::parse($idx->data_atribuicao)->format('d/m/Y H:i') }}</td>
                                    <td class="text-left">
                                        {{ optional($idx->item)->nome }}
                                        @if ($fabricacaoAuto)
                                            <span class="badge badge-warning ml-1">FABRICACÃO</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $idx->tipo }}</td>
                                    <td class="text-center">{{ $idx->quantidade }}</td>
                                    <td class="text-left">{{ optional($idx->bauOrigem)->nome }}</td>
                                    <td class="text-left">{{ optional($idx->bauDestino)->nome }}</td>
                                    <td class="text-left">{{ optional($idx->usuario)->nome }}</td>
                                    <td class="text-center">
                                        <div class="text-center table-actions">
                                            <a type="button" class="btn btn-sm btn-primary" title="Editar"
                                                href="{{ route('bau.lancamentos.edit', [$idx->id]) }}">
                                                <i class="ti-pencil"></i>Editar
                                            </a>
                                            <a type="button" class="btn btn-sm btn-danger" title="Excluir"
                                                onclick="mostrarConfirmacaoExclusao('Excluir Lançamento', 'Deseja realmente excluir o lançamento?', 'Excluir', 'Cancelar', {{ $idx->id }})">
                                                <i class="ti-trash"></i> Excluir
                                            </a>
                                            <form id="{{ $idx->id }}"
                                                action="{{ route('bau.lancamentos.destroy', [$idx->id]) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">Registros não encontrados</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="8">Total de Registros:
                                <strong>{{ method_exists($listLancamentos, 'total') ? $listLancamentos->total() : $listLancamentos->count() }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $listLancamentos->links('pagination::bootstrap-4') }}
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
