<?php

namespace App\Http\Controllers;

use App\Models\Baus;
use App\Models\DiscordSolicitacao;
use App\Models\Itens;
use App\Services\Contracts\LoggingServiceInterface;
use App\Services\LancamentoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SolicitacaoDiscordController extends Controller
{
    public function __construct(private LoggingServiceInterface $logger) {}

    public function index(Request $request): View
    {
        $status = strtolower((string) $request->get('status', DiscordSolicitacao::STATUS_PENDENTE));
        $tipo = strtoupper((string) $request->get('tipo', ''));
        $busca = trim((string) $request->get('busca', ''));

        $query = DiscordSolicitacao::query()
            ->when($status !== 'todos', fn($q) => $q->where('status', $status))
            ->when(in_array($tipo, ['ENTRADA', 'SAIDA', 'TRANSFERENCIA'], true), fn($q) => $q->where('tipo', $tipo))
            ->when($busca !== '', function ($q) use ($busca) {
                $q->where(function ($sub) use ($busca) {
                    $sub->where('id', (int) $busca)
                        ->orWhere('discord_username', 'like', '%' . $busca . '%')
                        ->orWhere('discord_message_id', 'like', '%' . $busca . '%')
                        ->orWhere('observacao', 'like', '%' . $busca . '%');
                });
            })
            ->orderByDesc('created_at');

        $solicitacoes = $query->paginate(15)->withQueryString();

        $statusResumo = DiscordSolicitacao::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        foreach ([DiscordSolicitacao::STATUS_PENDENTE, DiscordSolicitacao::STATUS_AJUSTE, DiscordSolicitacao::STATUS_APROVADA, DiscordSolicitacao::STATUS_REJEITADA] as $statusPadrao) {
            $statusResumo[$statusPadrao] = $statusResumo[$statusPadrao] ?? 0;
        }

        $result = view('controleBau.bau.solicitacoes.index', [
            'solicitacoes' => $solicitacoes,
            'statusSelecionado' => $status,
            'tipoSelecionado' => $tipo,
            'statusResumo' => $statusResumo,
        ]);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function edit(DiscordSolicitacao $solicitacao, LancamentoService $lancamentoService): View
    {
        $itens = Itens::query()->orderBy('nome')->get(['id', 'nome']);
        $baus = Baus::query()->orderBy('nome')->get(['id', 'nome']);
        $mapItens = $itens->pluck('nome', 'id');
        $mapBaus = $baus->pluck('nome', 'id');
        $mapaLimites = collect(config('anomalias.limites_baus', []));
        $limitePadrao = max(1, (int) config('anomalias.limite_padrao_bau', 1000));
        $percentualAviso = (float) config('anomalias.limite_percentual_bau', 0.8);

        $alertas = [];
        $itensColecao = collect($solicitacao->itens ?? []);
        $ocupacaoCacheOrigem = [];
        $ocupacaoCacheDestino = [];

        if ($itensColecao->isNotEmpty()) {
            foreach ($itensColecao as $index => $item) {
                $itemId = (int) Arr::get($item, 'itens_id');
                $quantidade = (int) Arr::get($item, 'quantidade');
                $bauOrigemId = (int) Arr::get($item, 'bau_origem_id', $solicitacao->bau_origem_id);
                $nomeItem = $mapItens->get($itemId, Arr::get($item, 'nome', Arr::get($item, 'descricao', 'Item #' . $itemId)));

                if (($solicitacao->tipo === 'SAIDA' || $solicitacao->tipo === 'TRANSFERENCIA') && $itemId > 0 && $bauOrigemId > 0) {
                    if (!array_key_exists($bauOrigemId, $ocupacaoCacheOrigem)) {
                        $ocupacaoCacheOrigem[$bauOrigemId] = [];
                    }
                    if (!array_key_exists($itemId, $ocupacaoCacheOrigem[$bauOrigemId])) {
                        $ocupacaoCacheOrigem[$bauOrigemId][$itemId] = $lancamentoService->obterSaldoItemNoBau($itemId, $bauOrigemId);
                    }
                    $saldo = $ocupacaoCacheOrigem[$bauOrigemId][$itemId];
                    if ($saldo < $quantidade) {
                        $nomeBauOrigem = $baus->firstWhere('id', $bauOrigemId)?->nome ?? "Báu {$bauOrigemId}";
                        $alertas[] = "{$nomeItem}: saldo disponível {$saldo} no báu de origem {$nomeBauOrigem}, quantidade solicitada {$quantidade}.";
                    }
                    $ocupacaoCacheOrigem[$bauOrigemId][$itemId] = max(0, $saldo - $quantidade);
                }

                if (($solicitacao->tipo === 'SAIDA' || $solicitacao->tipo === 'TRANSFERENCIA') && $itemId > 0 && $bauOrigemId <= 0) {
                    $alertas[] = "Informe o báu de origem para o item {$nomeItem}.";
                }

                if (($solicitacao->tipo === 'ENTRADA' || $solicitacao->tipo === 'TRANSFERENCIA')) {
                    $bauDestinoId = (int) Arr::get($item, 'bau_destino_id', $solicitacao->bau_destino_id);
                    if ($bauDestinoId <= 0) {
                        $alertas[] = "Informe o báu de destino para o item {$nomeItem}.";
                    } else {
                        $nomeBauDestino = $mapBaus->get($bauDestinoId) ?? "Báu {$bauDestinoId}";
                        $limiteDestino = $this->obterLimiteBau($bauDestinoId, $nomeBauDestino, $mapaLimites, $limitePadrao);

                        if (!array_key_exists($bauDestinoId, $ocupacaoCacheDestino)) {
                            $ocupacaoCacheDestino[$bauDestinoId] = $lancamentoService->obterOcupacaoBau($bauDestinoId);
                        }

                        $ocupacaoProjetada = $ocupacaoCacheDestino[$bauDestinoId] + $quantidade;

                        if ($limiteDestino > 0) {
                            $percentual = $ocupacaoProjetada / $limiteDestino;
                            if ($percentual >= 1) {
                                $alertas[] = "{$nomeBauDestino}: limite excedido ao receber {$quantidade} unidade(s) de {$nomeItem} ({$ocupacaoProjetada}/{$limiteDestino}).";
                            } elseif ($percentual >= $percentualAviso) {
                                $percentFormatado = number_format($percentual * 100, 1, ',', '.');
                                $alertas[] = "{$nomeBauDestino}: atingirá {$percentFormatado}% do limite ao receber {$quantidade} unidade(s) de {$nomeItem} ({$ocupacaoProjetada}/{$limiteDestino}).";
                            }
                        }

                        $ocupacaoCacheDestino[$bauDestinoId] = $ocupacaoProjetada;
                    }
                }
            }
        }

        return view('controleBau.bau.solicitacoes.edit', compact('solicitacao', 'itens', 'baus', 'alertas'));
    }

    public function update(Request $request, DiscordSolicitacao $solicitacao): RedirectResponse
    {
        $itensTratados = $this->normalizarItensEntrada($request->input('itens', []));
        $request->merge(['itens' => $itensTratados->values()->all()]);

        $dados = $request->validate([
            'observacao' => ['nullable', 'string', 'max:500'],
            'bau_origem_id' => ['nullable', 'integer', 'exists:BAUS,id'],
            'bau_destino_id' => ['nullable', 'integer', 'exists:BAUS,id'],
            'itens' => ['required', 'array', 'min:1'],
            'itens.*.itens_id' => ['required', 'integer', 'exists:ITENS,id'],
            'itens.*.quantidade' => ['required', 'integer', 'min:1'],
            'itens.*.bau_origem_id' => ['nullable', 'integer', 'exists:BAUS,id'],
            'itens.*.bau_destino_id' => ['nullable', 'integer', 'exists:BAUS,id'],
        ], [], [
            'itens.*.itens_id' => 'item',
            'itens.*.quantidade' => 'quantidade',
        ]);

        $tipo = strtoupper($solicitacao->tipo);
        $itensNormalizados = collect($dados['itens'])->map(function (array $item) use ($dados, $solicitacao, $tipo) {
            $id = (int) Arr::get($item, 'itens_id');
            $quantidade = (int) Arr::get($item, 'quantidade');
            $destino = (int) Arr::get($item, 'bau_destino_id', $dados['bau_destino_id'] ?? $solicitacao->bau_destino_id);
            $origem = (int) Arr::get($item, 'bau_origem_id', $dados['bau_origem_id'] ?? $solicitacao->bau_origem_id);

            return [
                'itens_id' => $id,
                'quantidade' => $quantidade,
                'bau_destino_id' => $tipo === 'ENTRADA' ? $destino : ($tipo === 'TRANSFERENCIA' ? $destino : null),
                'bau_origem_id' => $tipo === 'SAIDA' ? $origem : ($tipo === 'TRANSFERENCIA' ? $origem : null),
            ];
        })->values();

        if ($itensNormalizados->isEmpty()) {
            throw ValidationException::withMessages(['itens' => 'Informe ao menos um item válido.']);
        }

        $solicitacao->update([
            'observacao' => $dados['observacao'] ?? $solicitacao->observacao,
            'bau_origem_id' => $dados['bau_origem_id'] ?? $solicitacao->bau_origem_id,
            'bau_destino_id' => $dados['bau_destino_id'] ?? $solicitacao->bau_destino_id,
            'itens' => $itensNormalizados->all(),
            'status' => DiscordSolicitacao::STATUS_AJUSTE,
        ]);

        return redirect()
            ->route('bau.lancamentos.solicitacoes.edit', $solicitacao)
            ->with('success', 'Solicitação atualizada com sucesso.');
    }

    public function aprovar(Request $request, DiscordSolicitacao $solicitacao, LancamentoService $lancamentoService): RedirectResponse
    {
        if (!in_array($solicitacao->status, [DiscordSolicitacao::STATUS_PENDENTE, DiscordSolicitacao::STATUS_AJUSTE], true)) {
            return back()->withErrors('Somente solicitações pendentes ou em ajuste podem ser aprovadas.');
        }

        $itens = collect($solicitacao->itens ?? []);
        if ($itens->isEmpty()) {
            return back()->withErrors('Nenhum item foi configurado para esta solicitação. Ajuste antes de aprovar.');
        }

        $tipo = strtoupper($solicitacao->tipo);
        $usuarioId = Auth::id();
        $observacao = $solicitacao->observacao ?: 'Origem Discord #' . $solicitacao->discord_message_id;
        $mapaBaus = Baus::query()->pluck('nome', 'id');
        $mapaLimites = collect(config('anomalias.limites_baus', []));
        $limitePadrao = max(1, (int) config('anomalias.limite_padrao_bau', 1000));

        try {
            $lancamentosIds = DB::transaction(function () use ($itens, $tipo, $usuarioId, $observacao, $solicitacao, $lancamentoService, $mapaBaus, $mapaLimites, $limitePadrao) {
                $ids = [];
                $ocupacaoDestinoCache = [];
                foreach ($itens as $item) {
                    $dadosLancamento = [
                        'tipo' => $tipo,
                        'itens_id' => Arr::get($item, 'itens_id'),
                        'quantidade' => Arr::get($item, 'quantidade'),
                        'usuario_id' => $usuarioId,
                        'bau_origem_id' => Arr::get($item, 'bau_origem_id', $solicitacao->bau_origem_id),
                        'bau_destino_id' => Arr::get($item, 'bau_destino_id', $solicitacao->bau_destino_id),
                        'observacao' => $observacao,
                    ];

                    $this->validarDadosLancamento($dadosLancamento, $tipo);

                    if (in_array($tipo, ['SAIDA', 'TRANSFERENCIA'], true) && $dadosLancamento['itens_id'] && $dadosLancamento['bau_origem_id']) {
                        $saldoAtual = $lancamentoService->obterSaldoItemNoBau(
                            (int) $dadosLancamento['itens_id'],
                            (int) $dadosLancamento['bau_origem_id']
                        );

                        if ($saldoAtual < (int) $dadosLancamento['quantidade']) {
                            throw ValidationException::withMessages([
                                'quantidade' => 'Saldo insuficiente no báu de origem para o item selecionado (saldo atual: ' . $saldoAtual . ').',
                            ]);
                        }
                    }

                    if (in_array($tipo, ['ENTRADA', 'TRANSFERENCIA'], true)) {
                        $destinoId = (int) $dadosLancamento['bau_destino_id'];
                        if ($destinoId > 0) {
                            if (!array_key_exists($destinoId, $ocupacaoDestinoCache)) {
                                $ocupacaoDestinoCache[$destinoId] = $lancamentoService->obterOcupacaoBau($destinoId);
                            }

                            $nomeDestino = $mapaBaus->get($destinoId) ?? "Báu {$destinoId}";
                            $limiteDestino = $this->obterLimiteBau($destinoId, $nomeDestino, $mapaLimites, $limitePadrao);

                            if ($limiteDestino > 0) {
                                $projetado = $ocupacaoDestinoCache[$destinoId] + (int) $dadosLancamento['quantidade'];
                                if ($projetado > $limiteDestino) {
                                    throw ValidationException::withMessages([
                                        'quantidade' => "Báu de destino {$nomeDestino} excederá o limite ({$projetado}/{$limiteDestino}).",
                                    ]);
                                }
                                $ocupacaoDestinoCache[$destinoId] = $projetado;
                            }
                        }
                    }

                    $novo = $lancamentoService->salvar($dadosLancamento);
                    $ids[] = $novo->id;
                }

                $solicitacao->update([
                    'status' => DiscordSolicitacao::STATUS_APROVADA,
                    'processado_em' => now(),
                    'processado_por' => $usuarioId,
                    'lancamentos_ids' => $ids,
                ]);

                $this->logger->cadastro(
                    'DISCORD_SOLICITACAO',
                    'APROVAR',
                    'Solicitação #' . $solicitacao->id . ' aprovada. Lançamentos: ' . implode(', ', $ids),
                    $solicitacao->id
                );

                return $ids;
            });
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors('Erro ao aprovar a solicitação: ' . $e->getMessage());
        }

        $this->notificarDiscord($solicitacao->fresh(), 'aprovada');

        return redirect()
            ->route('bau.lancamentos.solicitacoes.index', $request->only('status', 'tipo'))
            ->with('success', 'Solicitação aprovada. Lançamentos gerados: ' . implode(', ', $lancamentosIds));
    }

    public function rejeitar(Request $request, DiscordSolicitacao $solicitacao): RedirectResponse
    {
        if ($solicitacao->status === DiscordSolicitacao::STATUS_REJEITADA) {
            return back()->withErrors('Solicitação já rejeitada anteriormente.');
        }

        $dados = $request->validate([
            'motivo' => ['required', 'string', 'max:500'],
        ]);

        $solicitacao->update([
            'status' => DiscordSolicitacao::STATUS_REJEITADA,
            'observacao' => trim(($solicitacao->observacao ?: '') . ' | Rejeição: ' . $dados['motivo']),
            'processado_em' => now(),
            'processado_por' => Auth::id(),
        ]);

        $this->logger->cadastro(
            'DISCORD_SOLICITACAO',
            'REJEITAR',
            'Solicitação #' . $solicitacao->id . ' rejeitada. Motivo: ' . $dados['motivo'],
            $solicitacao->id
        );

        $this->notificarDiscord($solicitacao->fresh(), 'rejeitada', $dados['motivo']);

        return redirect()
            ->route('bau.lancamentos.solicitacoes.index', $request->only('status', 'tipo'))
            ->with('success', 'Solicitação rejeitada.');
    }

    public function navbar(): JsonResponse
    {
        $pendentes = DiscordSolicitacao::query()
            ->whereIn('status', [DiscordSolicitacao::STATUS_PENDENTE, DiscordSolicitacao::STATUS_AJUSTE])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $items = $pendentes->map(function (DiscordSolicitacao $solicitacao) {
            return [
                'id' => $solicitacao->id,
                'tipo' => $solicitacao->tipo,
                'usuario' => $solicitacao->discord_username,
                'status' => $solicitacao->status,
                'recebido_em' => optional($solicitacao->created_at)->format('d/m H:i'),
                'observacao' => Str::limit((string) $solicitacao->observacao, 80),
            ];
        });

        return response()->json([
            'count' => (int) $pendentes->count(),
            'items' => $items,
        ]);
    }

    protected function normalizarItensEntrada($itens): Collection
    {
        return collect($itens ?? [])->filter(function ($item) {
            $descricao = Arr::get($item, 'descricao');
            $nome = Arr::get($item, 'nome');
            $itensId = Arr::get($item, 'itens_id');
            $quantidade = Arr::get($item, 'quantidade');

            return $itensId || $quantidade || $descricao || $nome;
        });
    }

    private static function validarDadosLancamento(array $dados, string $tipo): void
    {
        $erros = [];

        if (empty($dados['itens_id'])) {
            $erros['itens_id'] = 'Item obrigatório para gerar o lançamento.';
        }
        if (empty($dados['quantidade']) || (int) $dados['quantidade'] <= 0) {
            $erros['quantidade'] = 'Quantidade inválida.';
        }
        if ($tipo === 'ENTRADA' && empty($dados['bau_destino_id'])) {
            $erros['bau_destino_id'] = 'Informe o baú de destino para a entrada.';
        }
        if ($tipo === 'SAIDA' && empty($dados['bau_origem_id'])) {
            $erros['bau_origem_id'] = 'Informe o baú de origem para a saída.';
        }
        if ($tipo === 'TRANSFERENCIA' && (empty($dados['bau_origem_id']) || empty($dados['bau_destino_id']))) {
            $erros['bau_transferencia'] = 'Informe baú de origem e destino para transferência.';
        }

        if (!empty($erros)) {
            throw ValidationException::withMessages($erros);
        }
    }

    private function obterLimiteBau(int $bauId, ?string $bauNome, Collection $mapaLimites, int $limitePadrao): int
    {
        $valor = $mapaLimites->get((string) $bauId);

        if ($valor === null && $bauNome) {
            $valor = $mapaLimites->get($bauNome);
        }

        $valor = $valor ?? $limitePadrao;

        return max(1, (int) $valor);
    }

    private function notificarDiscord(DiscordSolicitacao $solicitacao, string $acao, ?string $motivo = null): void
    {
        $botToken = config('services.discord.bot_token');
        if (empty($botToken)) {
            report(new \RuntimeException('DISCORD_BOT_TOKEN nao configurado ao tentar notificar aprovacao/rejeicao.'));
            return;
        }

        $canalEntrada = config('services.discord.canal_entrada_id');
        $canalSaida = config('services.discord.canal_saida_id');
        $canalAprovada = config('services.discord.canal_aprovada_id');
        $canalReprovada = config('services.discord.canal_reprovada_id');

        $tipo = strtoupper((string) $solicitacao->tipo);
        $destinosPadrao = match ($tipo) {
            'SAIDA' => [$canalSaida],
            'ENTRADA' => [$canalEntrada],
            'TRANSFERENCIA' => [$canalSaida, $canalEntrada],
            default => [$canalEntrada, $canalSaida],
        };

        $destinos = match ($acao) {
            'aprovada' => $canalAprovada ? [$canalAprovada] : $destinosPadrao,
            'rejeitada' => $canalReprovada ? [$canalReprovada] : $destinosPadrao,
            default => $destinosPadrao,
        };

        $canais = collect($destinos)->filter()->unique();

        if ($canais->isEmpty()) {
            report(new \RuntimeException('Nenhum canal Discord configurado (CANAL_ENTRADA_ID/CANAL_SAIDA_ID/CANAL_MENSAGEM_APROVADA/CANAL_MENSAGEM_REPROVADA).'));
            return;
        }

        $solicitacao->loadMissing('aprovador');

        $emoji = $acao === 'aprovada' ? "\u{2705}" : "\u{274C}";
        $statusTexto = $acao === 'aprovada' ? 'aprovada' : 'rejeitada';
        $tipoFormatado = Str::title(strtolower($tipo));
        $referencia = $solicitacao->discord_message_id ?: ('#' . $solicitacao->id);
        $mencaoSolicitante = $solicitacao->discord_user_id ? "<@{$solicitacao->discord_user_id}>" : null;

        $anexosColecao = collect(Arr::get($solicitacao->payload ?? [], 'anexos', []))
            ->filter(fn($anexo) => is_array($anexo) && !empty(Arr::get($anexo, 'url')));
        $totalAnexos = $anexosColecao->count();
        $suffixAnexos = $totalAnexos > 0 ? sprintf(' (Anexos: %d)', $totalAnexos) : '';

        if ($acao === 'rejeitada') {
            $conviteEspecificar = $mencaoSolicitante
                ? "{$mencaoSolicitante}, poderia especificar melhor os detalhes da solicitação, por favor?"
                : 'Solicitante, poderia especificar melhor os detalhes da solicitação, por favor?';
            $conteudo = sprintf(
                '%s Solicitação de %s rejeitada! %s%s',
                $emoji,
                $tipoFormatado,
                $conviteEspecificar,
                $suffixAnexos
            );
        } else {
            $conteudo = sprintf(
                '%s Solicitação de %s %s!%s',
                $emoji,
                $tipoFormatado,
                $statusTexto,
                $suffixAnexos
            );
        }

        $cores = [
            'aprovada' => 0x57F287,
            'rejeitada' => 0xED4245,
        ];
        $cor = $cores[$acao] ?? 0x5865F2;

        $motivoLimpo = trim($motivo ?? 'Sem justificativa informada.');

        $descricao = $acao === 'aprovada'
            ? 'A solicitação foi aprovada e registrada no sistema.'
            : sprintf(
                'A solicitação foi rejeitada. Motivo: %s. Corrija e envie novamente, por favor.',
                Str::limit($motivoLimpo, 160)
            );

        if ($totalAnexos > 0) {
            $descricao .= $acao === 'aprovada'
                ? ' Foram enviados anexos para conferência.'
                : ' Verifique os anexos enviados para ajustar a solicitação.';
        }

        $solicitante = trim(($solicitacao->discord_username ?: 'N/D') . ($solicitacao->discord_user_id ? " (`{$solicitacao->discord_user_id}`)" : ''));
        $responsavel = optional($solicitacao->aprovador)->nome
            ?? ($solicitacao->processado_por ? 'Usuário #' . $solicitacao->processado_por : 'N/D');
        $processadoEm = optional($solicitacao->processado_em)->format('d/m/Y H:i');

        $itensColecao = collect($solicitacao->itens ?? []);
        $itensIds = $itensColecao->pluck('itens_id')->filter()->unique();
        $mapItens = $itensIds->isNotEmpty()
            ? Itens::query()->whereIn('id', $itensIds)->pluck('nome', 'id')
            : collect();

        $itensLista = $itensColecao->map(function ($item, $index) use ($mapItens) {
            $itemId = Arr::get($item, 'itens_id');
            $nome = Arr::get($item, 'nome')
                ?? Arr::get($item, 'descricao')
                ?? ($itemId ? ($mapItens->get($itemId) ?? ('Item #' . $itemId)) : 'Item');
            $quantidade = Arr::get($item, 'quantidade', '?');
            return sprintf('`%02d` • %s × %s', $index + 1, $nome, $quantidade);
        })->filter();

        $itensDescricao = $itensLista->isNotEmpty()
            ? Str::limit($itensLista->implode("\n"), 1024)
            : 'Nenhum item informado.';

        $anexosDescricao = $anexosColecao->isNotEmpty()
            ? Str::limit(
                $anexosColecao
                    ->values()
                    ->map(function ($anexo, $index) {
                        $nome = Arr::get($anexo, 'filename') ?? Arr::get($anexo, 'name') ?? ('Arquivo ' . ($index + 1));
                        $url = Arr::get($anexo, 'url') ?? Arr::get($anexo, 'proxy_url');
                        $tamanho = Arr::get($anexo, 'size');
                        $legendaTamanho = $tamanho ? sprintf(' (%.2f KB)', $tamanho / 1024) : '';
                        return sprintf('[%s](%s)%s', $nome, $url, $legendaTamanho);
                    })
                    ->implode("\n"),
                1024
            )
            : null;

        $fields = [
            [
                'name' => 'Solicitante',
                'value' => $solicitante,
                'inline' => true,
            ],
            [
                'name' => 'Responsável',
                'value' => trim($responsavel . ($processadoEm ? "\n`{$processadoEm}`" : '')),
                'inline' => true,
            ],
            [
                'name' => 'Tipo',
                'value' => $tipoFormatado,
                'inline' => true,
            ],
            [
                'name' => 'Itens',
                'value' => $itensDescricao,
                'inline' => false,
            ],
        ];

        if (!empty($solicitacao->observacao)) {
            $fields[] = [
                'name' => 'Observações',
                'value' => Str::limit($solicitacao->observacao, 1024),
                'inline' => false,
            ];
        }

        if ($anexosDescricao) {
            $fields[] = [
                'name' => 'Anexos',
                'value' => $anexosDescricao,
                'inline' => false,
            ];
        }

        if ($acao === 'rejeitada') {
            $motivoTexto = '> ' . str_replace(["\r\n", "\r", "\n"], "\n> ", $motivoLimpo);
            $fields[] = [
                'name' => 'Motivo da rejeição',
                'value' => Str::limit($motivoTexto, 1024),
                'inline' => false,
            ];
        } else {
            $lancamentos = collect($solicitacao->lancamentos_ids ?? [])
                ->filter()
                ->map(fn($id) => "`#{$id}`")
                ->implode(' • ');

            $fields[] = [
                'name' => 'Lançamentos gerados',
                'value' => $lancamentos !== '' ? $lancamentos : 'N/D',
                'inline' => false,
            ];
        }

        $guildId = Arr::get($solicitacao->payload ?? [], 'guild_id');
        if ($guildId && $solicitacao->discord_channel_id && $solicitacao->discord_message_id) {
            $link = sprintf(
                'https://discord.com/channels/%s/%s/%s',
                $guildId,
                $solicitacao->discord_channel_id,
                $solicitacao->discord_message_id
            );

            $fields[] = [
                'name' => 'Mensagem original',
                'value' => sprintf('[Abrir no Discord](%s)', $link),
                'inline' => false,
            ];
        }

        $fields = array_values(array_filter($fields, fn($field) => isset($field['value']) && $field['value'] !== ''));

        $tituloEmbed = sprintf('%s Solicitação %s', $emoji, $tipoFormatado);
        $footer = [
            'text' => sprintf('Solicitação #%d • Referência %s', $solicitacao->id, $referencia),
        ];

        $timestamp = optional($solicitacao->processado_em)->toIso8601String() ?? now()->toIso8601String();

        $allowedMentions = ['parse' => []];
        if ($acao === 'rejeitada' && $solicitacao->discord_user_id) {
            $allowedMentions['users'] = [(string) $solicitacao->discord_user_id];
        }

        $request = Http::withToken($botToken, 'Bot')
            ->acceptJson()
            ->timeout(5);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        $canais->each(function ($canalId) use ($request, $conteudo, $tituloEmbed, $descricao, $fields, $cor, $footer, $timestamp, $solicitacao, $statusTexto, $allowedMentions) {
            try {
                $response = $request->post("https://discord.com/api/v10/channels/{$canalId}/messages", [
                    'content' => $conteudo,
                    'allowed_mentions' => $allowedMentions,
                    'embeds' => [
                        [
                            'title' => $tituloEmbed,
                            'description' => $descricao,
                            'color' => $cor,
                            'fields' => $fields,
                            'footer' => $footer,
                            'timestamp' => $timestamp,
                        ],
                    ],
                ]);

                if ($response->failed()) {
                    report(new \RuntimeException(sprintf(
                        'Falha ao notificar Discord (solicitacao #%d canal %s): %s',
                        $solicitacao->id,
                        $canalId,
                        $response->body()
                    )));
                } else {
                    Log::info(sprintf(
                        'Solicitacao #%d %s notificada no canal Discord %s (mensagem %s).',
                        $solicitacao->id,
                        $statusTexto,
                        $canalId,
                        $solicitacao->discord_message_id ?: 'sem mensagem original'
                    ));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }
}
