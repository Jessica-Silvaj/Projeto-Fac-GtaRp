@extends('layouts.master', ['titulo' => 'Produtos', 'subtitulo' => 'Fabricação'])
@section('conteudo')
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-10">
                        @if (empty($produto->id))
                            <h3>Cadastrar Produtos</h3>
                        @else
                            <h3>Editar Produtos</h3>
                        @endif
                    </div>
                    <div class="col-md-2 text-right">
                        <a id="voltar-btn" type="button"
                            class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"
                            href="{{ route('administracao.fabricacao.produtos.index') }}">
                            <i class="ti-arrow-left"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
            <form class="form-material" id="produtoForm" action="{{ route('administracao.fabricacao.produtos.store') }}"
                method="POST" role="">
                <div class="card-block">
                    <div class="form-row justify-content-center align-center">
                        @csrf
                        <input type="hidden" name="sync_itens" value="1">
                        <input id="id" name="id" type="hidden" value="{{ old('id', $produto->id) }}">
                        <div class="form-group form-default form-static-label col-md-6">
                            <input type="text" id="nome" name="nome"
                                class="form-control @error('nome') is-invalid @enderror"
                                value="{{ old('nome', $produto->nome) }}">
                            <span class="form-bar"></span>
                            <label for="nome" class="float-label">Nome</label>
                            @error('nome')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <input type="text" min="0" id="quantidade" name="quantidade"
                                class="form-control @error('quantidade') is-invalid @enderror"
                                value="{{ old('quantidade', $produto->quantidade) }}">
                            <span class="form-bar"></span>
                            <label for="quantidade" class="float-label">Quantidade</label>
                            @error('quantidade')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group form-default form-static-label col-md-3">
                            <select name="ativo" id="ativo" class="form-control select2 @error('ativo') is-invalid @enderror">
                                <option value="">Selecione</option>
                                <option value="1" @selected(old('ativo', (string) $produto->ativo) === '1')>SIM</option>
                                <option value="0" @selected(old('ativo', (string) $produto->ativo) === '0')>NÃO</option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="ativo" class="float-label">Ativo</label>
                            @error('ativo')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <hr />
                    <div class="form-row">
                        <div class="col-12">
                            <h4>Materiais do Produto</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="tabela-itens">
                                    <thead>
                                        <tr>
                                            <th class="col-md-8">Item</th>
                                            <th class="col-md-2 text-center">Quantidade</th>
                                            <th class="col-md-2 text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $componentes = old(
                                                'itens',
                                                isset($produto)
                                                    ? $produto->itens
                                                        ->map(
                                                            fn($i) => [
                                                                'id' => $i->id,
                                                                'nome' => $i->nome,
                                                                'quantidade' => $i->pivot->quantidade,
                                                            ],
                                                        )
                                                        ->toArray()
                                                    : [],
                                            );
                                        @endphp
                                        @forelse($componentes as $i => $comp)
                                            <tr>
                                                <td>
                                                    <select name="itens[{{ $i }}][id]" class="form-control select2" data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}" placeholder="Selecione">
                                                        <option value=""></option>
                                                        @if (!empty($comp['id']))
                                                            <option value="{{ $comp['id'] }}" selected>{{ $comp['nome'] ?? '' }}</option>
                                                        @endif
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="text" min="1"
                                                        name="itens[{{ $i }}][quantidade]" class="form-control"
                                                        value="{{ $comp['quantidade'] ?? 1 }}" />
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="removerLinha(this)"><i class="ti-trash"></i>
                                                        Remover</button>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary" onclick="adicionarLinha()"><i
                                    class="ti-plus"></i> Adicionar Material</button>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            @can('acesso', 'administracao.fabricacao.produtos.store')
                                <button type="submit" id="save-btn"
                                    class="btn btn-sm btn-primary btn-out-dashed waves-effect waves-light"><i
                                        class="ti-save"></i>Salvar</button>
                            @endcan
                            <a id="cancel-btn" class="btn btn-sm btn-danger btn-out-dashed waves-effect waves-light"
                                href="{{ route('administracao.fabricacao.produtos.edit', [$produto->id]) }}"><i
                                    class="ti-close"></i>Cancelar
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
        function removerLinha(btn) {
            const tr = btn.closest('tr');
            const tbody = tr.parentElement;
            tr.remove();
            reindexInputs(tbody);
        }

        function adicionarLinha() {
            const tbody = document.querySelector('#tabela-itens tbody');
            const idx = tbody.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
            <td>
                <select name="itens[${idx}][id]" class="form-control select2" data-ajax-url="{{ route('administracao.fabricacao.produtos.itens.search') }}" placeholder="Selecione">
                    <option value=""></option>
                </select>
            </td>
            <td>
                <input type="text" min="1" name="itens[${idx}][quantidade]" class="form-control" value="1" />
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removerLinha(this)"><i class="ti-trash"></i> Remover</button>
            </td>
        `;
            tbody.appendChild(tr);
        }

        function reindexInputs(tbody) {
            [...tbody.querySelectorAll('tr')].forEach((tr, i) => {
                const sel = tr.querySelector('select');
                const qty = tr.querySelector('input[type="text"]');
                sel.setAttribute('name', `itens[${i}][id]`);
                qty.setAttribute('name', `itens[${i}][quantidade]`);
            });
        }
    </script>
@endsection
