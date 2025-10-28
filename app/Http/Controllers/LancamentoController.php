<?php

namespace App\Http\Controllers;

use App\Http\Requests\LancamentoRequest;
use App\Models\Baus;
use App\Models\Usuario;
use App\Services\Contracts\LancamentoServiceInterface;
use App\Services\Contracts\LoggingServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LancamentoController extends Controller
{
    public function __construct(
        private LancamentoServiceInterface $service,
        private LoggingServiceInterface $logger
    ) {}

    public function index(Request $request)
    {
        $listLancamentos = $this->service->listar($request);
        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return view('controleBau.bau.lancamentos.index', compact('listLancamentos'));
    }

    public function edit(int $id = 0)
    {
        $data = $this->service->dadosEdicao($id);
        $result = view('controleBau.bau.lancamentos.edit', $data);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function store(LancamentoRequest $request)
    {
        try {
            $this->service->salvar($request->validated());
            return redirect()->route('bau.lancamentos.index')->with('success', 'O lancamento foi salvo com sucesso.');
        } catch (ValidationException $e) {
            $mensagem = collect($e->errors())->flatten()->first();
            return redirect()
                ->back()
                ->withInput()
                ->withErrors($e->errors())
                ->with('error', $mensagem ?? 'Falha ao validar os dados do lancamento.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao salvar o lancamento.');
        } finally {
            // Garantir desconexão da base de dados
            DB::disconnect('mysql');
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->service->excluir($id);
            return redirect()->back()->with('success', 'O lancamento foi excluido com sucesso.');
        } catch (\Throwable $e) {
            $this->logger->excecao($e);
            return redirect()->back()->with('error', 'Ocorreu um erro ao excluir o lancamento.');
        } finally {
            DB::disconnect('mysql');
        }
    }

    public function searchBaus(Request $request)
    {
        $term = trim((string) $request->get('q', ''));
        $query = Baus::query()->where('ativo', 1);
        if ($term !== '') {
            $query->where('nome', 'LIKE', '%' . Str::upper($term) . '%');
        }

        $items = $query->orderBy('nome')->limit(20)->get(['id', 'nome']);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return response()->json([
            'results' => $items->map(fn($i) => ['id' => $i->id, 'text' => $i->nome]),
        ]);
    }

    public function historico(Request $request)
    {
        $data = $this->service->historico($request);
        $result = view('controleBau.bau.lancamentos.historico', $data);

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function historicoCsv(Request $request)
    {
        $data = $this->service->historico($request);
        $dataset = (string) $request->get('dataset', 'serie'); // serie|top_entradas|top_saidas
        $modo = (string) ($data['modo'] ?? 'quantidade');
        $granularidade = (string) ($data['granularidade'] ?? 'dia');

        $filename = 'historico_' . $dataset . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($dataset, $data, $modo, $granularidade) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($dataset === 'top_entradas') {
                fputcsv($out, ['Item', $modo === 'movimentos' ? 'Movimentacoes' : 'Quantidade', 'Tipo'], ';');
                foreach (($data['entradasPorItemTodos'] ?? $data['entradasPorItem'] ?? []) as $row) {
                    fputcsv($out, [$row['label'] ?? '', (int) ($row['value'] ?? 0), 'Entradas'], ';');
                }
            } elseif ($dataset === 'top_saidas') {
                fputcsv($out, ['Item', $modo === 'movimentos' ? 'Movimentacoes' : 'Quantidade', 'Tipo'], ';');
                foreach (($data['saidasPorItemTodos'] ?? $data['saidasPorItem'] ?? []) as $row) {
                    fputcsv($out, [$row['label'] ?? '', (int) ($row['value'] ?? 0), 'Saidas'], ';');
                }
            } else {
                fputcsv($out, ['Periodo', 'Entradas', 'Saidas', 'Metrica', 'Granularidade'], ';');
                foreach (($data['serie'] ?? []) as $row) {
                    fputcsv(
                        $out,
                        [$row['y'] ?? '', (int) ($row['entradas'] ?? 0), (int) ($row['saidas'] ?? 0), $modo, $granularidade],
                        ';'
                    );
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function historicoJson(Request $request)
    {
        $data = $this->service->historico($request);
        $dataset = (string) $request->get('dataset', '');
        if ($dataset === '') {
            DB::disconnect('mysql');
            return response()->json($data);
        }

        $map = [
            'serie' => $data['serie'] ?? [],
            'serie_prev' => $data['seriePrev'] ?? [],
            'saldo' => $data['saldoSerie'] ?? [],
            'saldo_prev' => $data['saldoPrev'] ?? [],
            'top_entradas' => $data['entradasPorItemTodos'] ?? ($data['entradasPorItem'] ?? []),
            'top_saidas' => $data['saidasPorItemTodos'] ?? ($data['saidasPorItem'] ?? []),
            'top_baus_entradas' => $data['topBausEntradas'] ?? [],
            'top_baus_saidas' => $data['topBausSaidas'] ?? [],
            'detalhado' => $data['detalhes'] ?? [],
        ];

        DB::disconnect('mysql');
        return response()->json($map[$dataset] ?? []);
    }

    public function historicoDetalhes(Request $request)
    {
        $det = $this->service->detalhes($request);
        DB::disconnect('mysql');
        return response()->json($det);
    }

    public function estoqueTotal(Request $request)
    {
        $data = $this->service->estoqueTotal($request);
        DB::disconnect('mysql');
        return view('controleBau.bau.lancamentos.estoque-total', $data);
    }

    public function estoqueTotalCsv(Request $request)
    {
        $dataset = (string) $request->get('dataset', 'detalhes'); // detalhes|resumo_itens|resumo_baus
        $data = $this->service->estoqueTotal($request);

        $filename = 'estoque_total_' . $dataset . '_' . date('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($dataset, $data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($dataset === 'resumo_itens') {
                fputcsv($out, ['Item', 'Quantidade', 'Baus e Quantidades'], ';');
                foreach (($data['resumoItens'] ?? []) as $row) {
                    $locais = collect($row['locais'] ?? [])
                        ->map(fn($l) => ($l['bau_nome'] ?? '') . ': ' . ($l['quantidade'] ?? 0))
                        ->implode(' | ');
                    fputcsv($out, [$row['item_nome'] ?? '', (int) ($row['quantidade'] ?? 0), $locais], ';');
                }
            } elseif ($dataset === 'resumo_baus') {
                fputcsv($out, ['Bau', 'Quantidade', 'Itens e Quantidades'], ';');
                foreach (($data['resumoBaus'] ?? []) as $row) {
                    $itens = collect($row['itens_lista'] ?? [])
                        ->map(fn($i) => ($i['item_nome'] ?? '') . ': ' . ($i['quantidade'] ?? 0))
                        ->implode(' | ');
                    fputcsv($out, [$row['bau_nome'] ?? '', (int) ($row['quantidade'] ?? 0), $itens], ';');
                }
            } else {
                fputcsv($out, ['Item', 'Bau', 'Quantidade'], ';');
                foreach (($data['detalhes'] ?? []) as $row) {
                    fputcsv($out, [$row['item_nome'] ?? '', $row['bau_nome'] ?? '', (int) ($row['saldo'] ?? 0)], ';');
                }
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
