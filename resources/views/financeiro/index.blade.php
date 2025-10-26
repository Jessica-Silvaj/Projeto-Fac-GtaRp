@extends('layouts.master', ['titulo' => 'Controle Financeiro', 'subtitulo' => 'Visualização de dinheiro e repasses'])

@section('css')
@endsection

@section('conteudo')
    {{-- Filtros --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h3>Filtros do Sistema Financeiro</h3>
            </div>
            <form class="form-material" method="GET" role="form">
                <div class="card-block">
                    <div class="form-row">
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_inicio" name="data_inicio" class="form-control data-mask"
                                data-format="DD/MM/YYYY"
                                value="{{ old('data_inicio', request('data_inicio', date('d/m/Y', strtotime('-30 days')))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Início</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <input type="text" id="data_fim" name="data_fim" class="form-control data-mask"
                                data-format="DD/MM/YYYY" value="{{ old('data_fim', request('data_fim', date('d/m/Y'))) }}">
                            <span class="form-bar"></span>
                            <label class="float-label">Data Fim</label>
                        </div>
                        <div class="form-group form-default form-static-label col-md-4">
                            <select name="status" id="status" class="form-control select2">
                                <option value="">Todos os Status</option>
                                <option value="pendente" {{ request('status') == 'pendente' ? 'selected' : '' }}>Pendente
                                </option>
                                <option value="repassado" {{ request('status') == 'repassado' ? 'selected' : '' }}>Repassado
                                </option>
                            </select>
                            <span class="form-bar"></span>
                            <label for="status" class="float-label">Status do Repasse</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row justify-content-center">
                        <div class="form-group col-sm-12 text-center">
                            <button type="submit" class="btn btn-success btn-sm btn-out-dashed waves-effect waves-light">
                                <i class="ti-search"></i> Filtrar
                            </button>
                            <a class="btn btn-primary btn-sm btn-out-dashed waves-effect waves-light"
                                href="{{ route('financeiro.index') }}">
                                <i class="ti-close"></i> Limpar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Cards de Resumo --}}
    <div class="col-sm-12">
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-success text-white rounded">
                                <i class="ti-money" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">${{ number_format($resumoGeral->total_limpo ?? 0, 0, ',', '.') }}</h4>
                                <span class="text-muted">Dinheiro Limpo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-warning text-white rounded">
                                <i class="ti-wallet" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">${{ number_format($resumoGeral->total_sujo ?? 0, 0, ',', '.') }}</h4>
                                <span class="text-muted">Dinheiro Sujo</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-info text-white rounded">
                                <i class="ti-shopping-cart" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">{{ number_format($resumoGeral->total_vendas ?? 0, 0, ',', '.') }}</h4>
                                <span class="text-muted">Total de Vendas</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-block p-3">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-primary text-white rounded">
                                <i class="ti-stats-up" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="ml-3">
                                <h4 class="mb-0">
                                    ${{ number_format(($resumoGeral->total_limpo ?? 0) + ($resumoGeral->total_sujo ?? 0), 0, ',', '.') }}
                                </h4>
                                <span class="text-muted">Total Geral</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabela de Vendedores --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0">Dinheiro por Vendedor</h5>
                    <small class="text-muted">Controle de repasses e pagamentos</small>
                </div>
                <div>
                    <a href="{{ route('financeiro.relatorio') }}"
                        class="btn btn-sm btn-info btn-out-dashed waves-effect waves-light">
                        <i class="ti-bar-chart mr-1"></i> Relatório Completo
                    </a>
                </div>
            </div>
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-responsive-md">
                        <thead>
                            <tr>
                                <th class="text-left col-md-3">Vendedor</th>
                                <th class="text-center col-md-2">Dinheiro Limpo</th>
                                <th class="text-center col-md-2">Dinheiro Sujo</th>
                                <th class="text-center col-md-2">Total</th>
                                <th class="text-center col-md-1">Vendas</th>
                                <th class="text-center col-md-1">Status</th>
                                <th class="text-center col-md-1">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vendasPorVendedor as $vendedor)
                                @php
                                    $total = $vendedor->total_limpo + $vendedor->total_sujo;
                                @endphp
                                <tr>
                                    <td class="text-left col-md-3">
                                        <strong>{{ $vendedor->nome }}</strong>
                                    </td>
                                    <td class="text-center col-md-2">
                                        <span class="text-success">
                                            ${{ number_format($vendedor->total_limpo, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center col-md-2">
                                        <span class="text-warning">
                                            ${{ number_format($vendedor->total_sujo, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center col-md-2">
                                        <strong class="text-primary">
                                            ${{ number_format($total, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-center col-md-1">
                                        <span class="pcoded-badge label label-info">
                                            {{ $vendedor->total_vendas }}
                                        </span>
                                    </td>
                                    <td class="text-center col-md-1">
                                        @if ($vendedor->repassado)
                                            <span class="pcoded-badge label label-success">
                                                Repassado
                                            </span>
                                        @else
                                            <span class="pcoded-badge label label-danger">
                                                Pendente
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center col-md-1">
                                        <div class="text-center table-actions">
                                            @if (!$vendedor->repassado)
                                                {{-- Botões para vendedores pendentes --}}
                                                @can('acesso', 'financeiro.repasse')
                                                    <button class="btn btn-sm btn-success btn-repasse"
                                                        data-vendedor-id="{{ $vendedor->id }}"
                                                        data-vendedor-nome="{{ $vendedor->nome }}"
                                                        data-valor="{{ $total }}"
                                                        data-limpo-disponivel="{{ $vendedor->total_limpo }}"
                                                        data-sujo-disponivel="{{ $vendedor->total_sujo }}"
                                                        data-total-vendas="{{ $vendedor->total_vendas }}"
                                                        title="Marcar Repasse">
                                                        <i class="ti-money mr-1"></i> Repasse
                                                    </button>
                                                @endcan
                                            @else
                                                {{-- Botões para vendedores já repassados --}}
                                                <button class="btn btn-sm btn-danger btn-reverter"
                                                    data-vendedor-id="{{ $vendedor->id }}"
                                                    data-vendedor-nome="{{ $vendedor->nome }}" title="Desfazer Repasse">
                                                    <i class="ti-back-left mr-1"></i> Desfazer Repasse
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        Registros não encontrados
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <td colspan="7">
                                Total de Registros: <strong>{{ count($vendasPorVendedor) }}</strong>
                            </td>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Card de Informações --}}
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="ti-info-alt text-primary"></i> Informações do Sistema</h5>
                <small class="text-muted">Dicas importantes para o controle financeiro</small>
            </div>
            <div class="card-block">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-info shadow-sm border-left-primary"
                            style="border-left: 4px solid #3498db; background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%); padding: 20px;">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-info text-white rounded-circle mr-3"
                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ti-search" style="font-size: 18px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-info font-weight-bold">💡 Dica de Filtros</h6>
                                    <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.4;">
                                        Use os filtros acima para encontrar vendedores específicos ou períodos determinados.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-warning shadow-sm border-left-warning"
                            style="border-left: 4px solid #f39c12; background: linear-gradient(135deg, #fff3cd 0%, #f8f9fa 100%); padding: 20px;">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-warning text-white rounded-circle mr-3"
                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ti-shield" style="font-size: 18px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-warning font-weight-bold">⚠️ Controle de Acesso</h6>
                                    <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.4;">
                                        Repasses concluídos só podem ser revertidos com permissão específica de
                                        administrador.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="alert alert-success shadow-sm border-left-success"
                            style="border-left: 4px solid #27ae60; background: linear-gradient(135deg, #d4edda 0%, #f8f9fa 100%); padding: 20px;">
                            <div class="d-flex align-items-center">
                                <div class="p-2 bg-success text-white rounded-circle mr-3"
                                    style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ti-check-box" style="font-size: 18px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-success font-weight-bold">✅ Verificação</h6>
                                    <p class="mb-0 text-dark" style="font-size: 14px; line-height: 1.4;">
                                        Sempre confira os valores e usuário destinatário antes de confirmar um repasse.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Linha adicional com informações técnicas --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-light border shadow-sm"
                            style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-left: 4px solid #6c757d; padding: 15px;">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1 text-secondary font-weight-bold">
                                        <i class="ti-info text-secondary mr-2"></i>Informações Técnicas
                                    </h6>
                                    <small class="text-muted">
                                        • Os valores são calculados automaticamente baseados nas vendas concluídas
                                        • O status "Pendente" indica vendedores que ainda não receberam repasse
                                        • Use o filtro "Repassado" para ver o histórico de repasses realizados
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="{{ route('financeiro.relatorio') }}"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="ti-bar-chart mr-1"></i> Ver Relatório Completo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para confirmar repasse --}}
    <div class="modal fade" id="modalRepasse" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Repasse</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-primary">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2">
                                    <i class="ti-user mr-2"></i><strong id="nomeVendedor"></strong>
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <small><strong>Dinheiro Limpo:</strong> R$ <span
                                                id="limpoDisponivel">0</span></small>
                                    </div>
                                    <div class="col-md-6">
                                        <small><strong>Dinheiro Sujo:</strong> R$ <span
                                                id="sujoDisponivel">0</span></small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="pcoded-badge label label-primary p-2">
                                    <strong>Total: R$ <span id="valorRepasse">0</span></strong><br>
                                    <small><span id="totalVendas">0</span> vendas</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="usuarioRepasse">Usuário que receberá o repasse <span
                                        class="text-danger">*</span>:</label>
                                <select id="usuarioRepasse" class="form-control select2">
                                    <option value="">Selecione um usuário...</option>
                                    @if (isset($usuariosSelect))
                                        @foreach ($usuariosSelect as $usuario)
                                            <option value="{{ $usuario->id }}">{{ $usuario->nome }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipo de dinheiro a repassar <span class="text-danger">*</span>:</label>
                                <div class="form-check-inline">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" id="repassarLimpo" checked>
                                        <span class="form-check-sign">Dinheiro Limpo</span>
                                    </label>
                                </div>
                                <div class="form-check-inline ml-3">
                                    <label class="form-check-label">
                                        <input type="checkbox" class="form-check-input" id="repassarSujo" checked>
                                        <span class="form-check-sign">Dinheiro Sujo</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row" id="valoresRepasse">
                        <div class="col-md-6" id="containerValorLimpo">
                            <div class="form-group">
                                <label for="valorLimpoInput">Valor Limpo <span class="text-danger">*</span>:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" id="valorLimpoInput" class="form-control money-input"
                                        placeholder="0" data-tipo="limpo">
                                </div>
                                <small class="form-text text-muted">
                                    Disponível: R$ <span id="disponivelLimpo">0</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-6" id="containerValorSujo">
                            <div class="form-group">
                                <label for="valorSujoInput">Valor Sujo <span class="text-danger">*</span>:</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">R$</span>
                                    </div>
                                    <input type="text" id="valorSujoInput" class="form-control money-input"
                                        placeholder="0" data-tipo="sujo">
                                </div>
                                <small class="form-text text-muted">
                                    Disponível: R$ <span id="disponivelSujo">0</span>
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observacoesRepasse">Observações sobre o repasse:</label>
                        <textarea id="observacoesRepasse" class="form-control" rows="3"
                            placeholder="Ex: Repasse referente às vendas de [período] - [motivo específico]..."></textarea>
                        <small class="form-text text-muted">
                            Descreva o motivo ou período do repasse para controle interno
                        </small>
                    </div>

                    <div class="alert alert-success" id="resumoRepasse">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <i class="ti-receipt" style="font-size: 1.5rem;"></i>
                            </div>
                            <div class="col-md-11">
                                <h6 class="mb-2">
                                    <strong><i class="ti-clipboard"></i> Resumo do Repasse</strong>
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="text-center p-2 border rounded">
                                            <small><strong>Limpo</strong></small><br>
                                            <strong>R$ <span id="resumoLimpo">0</span></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-2 border rounded">
                                            <small><strong>Sujo</strong></small><br>
                                            <strong>R$ <span id="resumoSujo">0</span></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-2 bg-success text-white rounded">
                                            <small><strong>TOTAL</strong></small><br>
                                            <strong style="font-size: 1.1rem;">R$ <span id="resumoTotal">0</span></strong>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block text-center">
                                    <i class="ti-arrow-right"></i> Este valor será transferido para o usuário selecionado
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmarRepasse">
                        <i class="ti-check"></i> Confirmar Repasse
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            let vendedorId = null;

            // Inicializar componentes do sistema
            $('.data-mask').mask('00/00/0000');



            // Abrir modal de repasse
            $('.btn-repasse').on('click', function() {
                vendedorId = $(this).data('vendedor-id');
                const nomeVendedor = $(this).data('vendedor-nome');
                const valor = $(this).data('valor');
                const totalVendas = $(this).data('total-vendas') || 1;

                // Preencher informações do modal
                $('#nomeVendedor').text(nomeVendedor);
                $('#valorRepasse').text(parseFloat(valor).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#totalVendas').text(totalVendas);
                $('#maxVendas').text(totalVendas);

                // Configurar valores disponíveis (buscar do botão clicado)
                const limpoDisponivel = $(this).data('limpo-disponivel') || (valor * 0.3);
                const sujoDisponivel = $(this).data('sujo-disponivel') || (valor * 0.7);

                // Atualizar no alerta superior e nos campos
                $('#limpoDisponivel').text(parseFloat(limpoDisponivel).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#sujoDisponivel').text(parseFloat(sujoDisponivel).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#disponivelLimpo').text(parseFloat(limpoDisponivel).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#disponivelSujo').text(parseFloat(sujoDisponivel).toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));

                // Valores sugeridos (30% do disponível)
                const sugestaoLimpo = Math.floor(limpoDisponivel * 0.3);
                const sugestaoSujo = Math.floor(sujoDisponivel * 0.3);

                $('#valorLimpoInput').val(sugestaoLimpo.toLocaleString('pt-BR'));
                $('#valorSujoInput').val(sugestaoSujo.toLocaleString('pt-BR'));

                // Atualizar resumo inicial
                atualizarResumoRepasse();

                // Limpar outros campos do modal
                $('#usuarioRepasse').val('').trigger('change');
                $('#observacoesRepasse').val('');

                // Armazenar dados para cálculos
                window.dadosVendedor = {
                    id: vendedorId,
                    nome: nomeVendedor,
                    valorTotal: parseFloat(valor),
                    totalVendas: totalVendas
                };


                $('#modalRepasse').modal('show');
            });

            // Função para atualizar o resumo do repasse
            function atualizarResumoRepasse() {
                const repassarLimpo = $('#repassarLimpo').is(':checked');
                const repassarSujo = $('#repassarSujo').is(':checked');

                const valorLimpo = repassarLimpo ? converterParaNumero($('#valorLimpoInput').val()) : 0;
                const valorSujo = repassarSujo ? converterParaNumero($('#valorSujoInput').val()) : 0;
                const valorTotal = valorLimpo + valorSujo;

                $('#resumoLimpo').text(valorLimpo.toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#resumoSujo').text(valorSujo.toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));
                $('#resumoTotal').text(valorTotal.toLocaleString('pt-BR', {
                    minimumFractionDigits: 0
                }));

                // Armazenar valores para validação
                window.valoresRepasse = {
                    limpo: valorLimpo,
                    sujo: valorSujo,
                    total: valorTotal
                };
            }

            // Função para converter texto formatado em número
            function converterParaNumero(texto) {
                if (!texto || texto === '') return 0;
                return parseInt(texto.toString().replace(/\D/g, '')) || 0;
            }

            // Gerenciar visibilidade dos campos baseado nos checkboxes
            $(document).on('change', '#repassarLimpo', function() {
                if ($(this).is(':checked')) {
                    $('#containerValorLimpo').show();
                } else {
                    $('#containerValorLimpo').hide();
                    $('#valorLimpoInput').val('0');
                }
                atualizarResumoRepasse();
            });

            $(document).on('change', '#repassarSujo', function() {
                if ($(this).is(':checked')) {
                    $('#containerValorSujo').show();
                } else {
                    $('#containerValorSujo').hide();
                    $('#valorSujoInput').val('0');
                }
                atualizarResumoRepasse();
            });

            // Formatação e validação dos campos de valor
            $(document).on('input', '.money-input', function() {
                let valor = $(this).val().replace(/\D/g, ''); // Remove tudo que não é dígito

                if (valor === '') {
                    $(this).val('0');
                    atualizarResumoRepasse();
                    return;
                }

                // Formatar como dinheiro brasileiro
                valor = parseInt(valor).toLocaleString('pt-BR');
                $(this).val(valor);

                atualizarResumoRepasse();
            });

            // Validar se o valor não excede o disponível
            $(document).on('blur', '.money-input', function() {
                const tipo = $(this).data('tipo');
                const valor = converterParaNumero($(this).val());
                const disponivel = converterParaNumero($('#disponivel' + (tipo === 'limpo' ? 'Limpo' :
                    'Sujo')).text());

                if (valor > disponivel) {
                    $(this).addClass('is-invalid');
                    if (!$(this).parent().next('.invalid-feedback').length) {
                        $(this).parent().after(
                            '<div class="invalid-feedback">Valor excede o disponível</div>');
                    }
                } else {
                    $(this).removeClass('is-invalid');
                    $(this).parent().next('.invalid-feedback').remove();
                }
            });

            // Confirmar repasse
            $('#confirmarRepasse').on('click', function() {
                if (!vendedorId) return;

                const usuarioRepasseId = $('#usuarioRepasse').val();
                const observacoes = $('#observacoesRepasse').val();
                const repassarLimpo = $('#repassarLimpo').is(':checked');
                const repassarSujo = $('#repassarSujo').is(':checked');

                // Validações
                if (!usuarioRepasseId) {
                    Swal.fire({
                        title: 'Atenção!',
                        text: 'Por favor, selecione um usuário para receber o repasse.',
                        icon: 'warning',
                        confirmButtonText: 'Entendi'
                    });
                    return;
                }

                if (!repassarLimpo && !repassarSujo) {
                    Swal.fire({
                        title: 'Selecione o Tipo',
                        text: 'Marque pelo menos uma opção: Dinheiro Limpo ou Dinheiro Sujo.',
                        icon: 'info',
                        confirmButtonText: 'Entendi'
                    });
                    return;
                }

                // Validar valores
                if (!window.valoresRepasse || window.valoresRepasse.total <= 0) {
                    Swal.fire({
                        title: 'Valores Inválidos',
                        text: 'Informe valores maiores que zero para realizar o repasse.',
                        icon: 'error',
                        confirmButtonText: 'Corrigir'
                    });
                    return;
                }

                // Verificar se há campos com erro
                if ($('.money-input.is-invalid').length > 0) {
                    Swal.fire({
                        title: 'Valores Excedentes',
                        text: 'Alguns valores excedem o saldo disponível. Corrija antes de continuar.',
                        icon: 'warning',
                        confirmButtonText: 'Corrigir'
                    });
                    return;
                }

                const btn = $(this);
                const originalText = btn.html();

                btn.prop('disabled', true).html('<i class="ti-reload"></i> Processando...');

                $.ajax({
                    url: `{{ url('financeiro/repasse') }}/${vendedorId}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        usuario_repasse_id: usuarioRepasseId,
                        valor_limpo: window.valoresRepasse.limpo,
                        valor_sujo: window.valoresRepasse.sujo,
                        observacoes: observacoes
                    },
                    success: function(response) {
                        if (response.success) {
                            // Preparar informações de status
                            let statusInfo = '';
                            if (response.data.vendedor_status_atualizado || response.data
                                .receptor_status_atualizado) {
                                statusInfo = '<hr><div class="mt-3">';
                                if (response.data.vendedor_status_atualizado) {
                                    statusInfo +=
                                        `<p class="mb-1"><i class="fas fa-user"></i> <strong>Seu status:</strong> <span class="badge badge-info">${response.data.vendedor_status_atualizado}</span></p>`;
                                }
                                if (response.data.receptor_status_atualizado) {
                                    statusInfo +=
                                        `<p class="mb-1"><i class="fas fa-user-check"></i> <strong>Status do receptor:</strong> <span class="badge badge-success">${response.data.receptor_status_atualizado}</span></p>`;
                                }
                                statusInfo += '</div>';
                            }

                            Swal.fire({
                                title: 'Repasse Realizado!',
                                html: `
                                    <div class="text-center">
                                        <p><strong>Transferência concluída com sucesso!</strong></p>
                                        <div class="row mt-3">
                                            <div class="col-6">
                                                <small class="text-muted">Dinheiro Limpo</small><br>
                                                <strong class="text-success">R$ ${response.data.valor_limpo.toLocaleString('pt-BR')}</strong>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Dinheiro Sujo</small><br>
                                                <strong class="text-warning">R$ ${response.data.valor_sujo.toLocaleString('pt-BR')}</strong>
                                            </div>
                                        </div>
                                        <hr>
                                        <p class="mb-0"><strong>Total: R$ ${response.data.valor_total.toLocaleString('pt-BR')}</strong></p>
                                        <small class="text-muted">Repassado para: ${response.data.usuario}</small>
                                        ${statusInfo}
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: 'Continuar'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Erro no Repasse',
                                text: response.message ||
                                    'Erro inesperado ao processar repasse',
                                icon: 'error',
                                confirmButtonText: 'Tentar Novamente'
                            });
                        }
                    },
                    error: function(xhr) {
                        const erro = xhr.responseJSON ? xhr.responseJSON.message :
                            'Erro interno do servidor';
                        Swal.fire({
                            title: 'Erro Técnico',
                            html: `
                                <div class="text-center">
                                    <p><strong>Não foi possível processar o repasse</strong></p>
                                    <div class="alert alert-danger mt-3">
                                        <small>${erro}</small>
                                    </div>
                                    <p class="text-muted mb-0">Tente novamente ou contate o suporte</p>
                                </div>
                            `,
                            icon: 'error',
                            confirmButtonText: 'Entendi'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalText);
                        $('#modalRepasse').modal('hide');
                    }
                });
            });





            // Botão Desfazer Repasse
            $('.btn-reverter').on('click', function() {
                const vendedorId = $(this).data('vendedor-id');
                const vendedorNome = $(this).data('vendedor-nome');

                Swal.fire({
                    title: 'Desfazer Repasse',
                    html: `
                        <div class="text-center">
                            <p><strong>Tem certeza que deseja desfazer o repasse?</strong></p>
                            <div class="alert alert-warning mt-3">
                                <strong>Vendedor:</strong> ${vendedorNome}<br>
                                <small>O repasse será marcado como desfeito e os valores voltarão ao saldo original</small>
                            </div>
                            <p class="text-muted mb-0">Esta ação pode ser revertida posteriormente</p>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, desfazer',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Implementar lógica de reversão
                        $.ajax({
                            url: `{{ url('financeiro/repasse') }}/${vendedorId}`,
                            method: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Preparar informações de status
                                    let statusInfo = '';
                                    if (response.data && (response.data
                                            .vendedor_status_atualizado || response.data
                                            .receptor_status_atualizado)) {
                                        statusInfo =
                                            '<div class="mt-3 p-3 bg-light rounded">';
                                        statusInfo +=
                                            '<h6 class="mb-2"><i class="fas fa-sync-alt"></i> Status Atualizados:</h6>';
                                        if (response.data.vendedor_status_atualizado) {
                                            statusInfo +=
                                                `<p class="mb-1"><strong>Vendedor:</strong> <span class="badge badge-info">${response.data.vendedor_status_atualizado}</span></p>`;
                                        }
                                        if (response.data.receptor_status_atualizado) {
                                            statusInfo +=
                                                `<p class="mb-1"><strong>Receptor:</strong> <span class="badge badge-success">${response.data.receptor_status_atualizado}</span></p>`;
                                        }
                                        if (response.data.valor_desfeito) {
                                            statusInfo +=
                                                `<p class="mb-0 text-muted"><small>Valor desfeito: R$ ${response.data.valor_desfeito.toLocaleString('pt-BR')}</small></p>`;
                                        }
                                        statusInfo += '</div>';
                                    }

                                    Swal.fire({
                                        title: 'Repasse Desfeito!',
                                        html: `
                                            <div class="text-center">
                                                <p><strong>O repasse foi desfeito com sucesso!</strong></p>
                                                <p class="text-muted">Os valores retornaram ao saldo original.</p>
                                                ${statusInfo}
                                            </div>
                                        `,
                                        icon: 'success',
                                        confirmButtonText: 'Continuar'
                                    });
                                    location.reload();
                                } else {
                                    Swal.fire({
                                        title: 'Erro',
                                        text: response.message ||
                                            'Não foi possível desfazer o repasse',
                                        icon: 'error',
                                        confirmButtonText: 'Entendi'
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: 'Erro Técnico',
                                    text: 'Erro interno do servidor. Tente novamente.',
                                    icon: 'error',
                                    confirmButtonText: 'Entendi'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

    <style>
        /* Estilo personalizado para o modal de repasse */
        .form-check-inline {
            margin-right: 1.5rem;
        }

        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }

        .money-input {
            text-align: right;
            font-weight: 500;
            font-family: 'Courier New', monospace;
        }

        /* Estilos dos alerts melhorados */
        .border-left-primary {
            border-left-color: #3498db !important;
        }

        .border-left-warning {
            border-left-color: #f39c12 !important;
        }

        .border-left-success {
            border-left-color: #27ae60 !important;
        }

        .alert {
            transition: all 0.3s ease;
        }

        .alert:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        }

        .alert h6 {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .alert p {
            font-size: 14px;
            line-height: 1.5;
            color: #2c3e50 !important;
        }

        /* Responsividade dos alerts */
        @media (max-width: 768px) {
            .alert {
                margin-bottom: 15px;
            }

            .alert .d-flex {
                flex-direction: column;
                text-align: center;
            }

            .alert .d-flex>div:first-child {
                margin-bottom: 10px;
                margin-right: 0 !important;
            }
        }

        /* Estilos do modal */
        .input-group-text {
            font-weight: 600;
        }

        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        @media (max-width: 768px) {
            .form-check-inline {
                display: block;
                margin-bottom: 0.5rem;
                margin-right: 0;
            }
        }
    </style>
@endsection
