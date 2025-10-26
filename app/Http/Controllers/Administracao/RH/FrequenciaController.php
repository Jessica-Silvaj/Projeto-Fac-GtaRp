<?php

namespace App\Http\Controllers\Administracao\RH;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Situacao;
use App\Models\Falta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FrequenciaController extends Controller
{
    public function index(Request $request)
    {
        // Filtro por data (padrão: hoje)
        $dataInput = $request->input('data_filtro');

        // Converter data do formato brasileiro (d/m/Y) para Y-m-d se necessário
        if ($dataInput && str_contains($dataInput, '/')) {
            $dataFiltro = Carbon::createFromFormat('d/m/Y', $dataInput)->format('Y-m-d');
        } else {
            $dataFiltro = $dataInput ?: now()->format('Y-m-d');
        }

        $situacaoId = $request->input('situacao_id');
        $usuarioId = $request->input('usuario_id');

        // Buscar apenas usuários ativos
        $query = Usuario::with(['situacao', 'perfil'])
            ->where('situacao_id', 1); // Apenas usuários ativos

        if ($situacaoId) {
            $query->where('situacao_id', $situacaoId);
        }

        if ($usuarioId) {
            $query->where('id', $usuarioId);
        }

        $usuarios = $query->orderBy('nome')->get();

        // Filtro de status de presença
        $statusPresenca = $request->input('status_presenca');

        // Para cada usuário, verificar se tem falta registrada na data
        $frequenciaUsuarios = $usuarios->map(function ($usuario) use ($dataFiltro) {
            // Verificar se tem falta registrada para a data
            $faltaHoje = Falta::temFalta($usuario->id, $dataFiltro);

            // Contar total de faltas do usuário (último mês)
            $totalFaltas = Falta::contarFaltasMes($usuario->id);

            // Status do usuário para o dia
            $statusHoje = $faltaHoje ? 'ausente' : 'presente';

            return [
                'usuario' => $usuario,
                'status_hoje' => $statusHoje,
                'falta_hoje' => $faltaHoje,
                'total_faltas_mes' => $totalFaltas,
                'pode_remover_falta' => $faltaHoje ? true : false
            ];
        });

        // Aplicar filtro de status de presença se selecionado
        if ($statusPresenca) {
            $frequenciaUsuarios = $frequenciaUsuarios->filter(function ($freq) use ($statusPresenca) {
                return $freq['status_hoje'] === $statusPresenca;
            });
        }

        // Ordenar por nome após filtrar
        $frequenciaUsuarios = $frequenciaUsuarios->sortBy('usuario.nome');

        // Dados para filtros
        $situacoes = Situacao::where('ativo', 1)->orderBy('nome')->get();
        $usuariosSelect = Usuario::where('situacao_id', 1)->orderBy('nome')->get();

        // Estatísticas do dia
        $totalUsuarios = $usuarios->count();
        $totalFaltasHoje = Falta::where('data_falta', $dataFiltro)
            ->where('ativo', 1)
            ->count();
        $totalPresentesHoje = $totalUsuarios - $totalFaltasHoje;

        $result = view('administracao.rh.frequencia.index', compact(
            'frequenciaUsuarios',
            'situacoes',
            'usuariosSelect',
            'dataFiltro',
            'situacaoId',
            'usuarioId',
            'statusPresenca',
            'totalUsuarios',
            'totalFaltasHoje',
            'totalPresentesHoje'
        ));

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function registrarFalta(Request $request)
    {
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'data_falta' => 'required|date|before_or_equal:today',
            'motivo' => 'required|string|min:5|max:500'
        ], [
            'usuario_id.required' => 'Usuário é obrigatório.',
            'usuario_id.exists' => 'Usuário não encontrado.',
            'data_falta.required' => 'Data é obrigatória.',
            'data_falta.before_or_equal' => 'Não é possível registrar falta para datas futuras.',
            'motivo.required' => 'É obrigatório informar o motivo da falta.',
            'motivo.min' => 'O motivo deve ter pelo menos 5 caracteres.'
        ]);

        try {
            // Verificar se já existe falta para a data
            if (Falta::temFalta($request->usuario_id, $request->data_falta)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma falta registrada para este usuário nesta data!'
                ], 422);
            }

            // Registrar falta
            Falta::registrarFalta($request->usuario_id, $request->data_falta, $request->motivo);

            $result = response()->json(['success' => true, 'message' => 'Falta registrada com sucesso!']);

            // Fechar conexão MySQL
            DB::disconnect('mysql');

            return $result;
        } catch (\Exception $e) {
            $result = response()->json([
                'success' => false,
                'message' => 'Erro ao registrar falta: ' . $e->getMessage()
            ], 500);

            // Fechar conexão MySQL mesmo em caso de erro
            DB::disconnect('mysql');

            return $result;
        }
    }

    public function removerFalta(Request $request)
    {
        // Se tiver falta_id, remover por ID
        if ($request->has('falta_id')) {
            try {
                $falta = Falta::findOrFail($request->falta_id);
                $falta->delete();

                $result = response()->json(['success' => true, 'message' => 'Falta removida com sucesso!']);

                // Fechar conexão MySQL
                DB::disconnect('mysql');

                return $result;
            } catch (\Exception $e) {
                $result = response()->json([
                    'success' => false,
                    'message' => 'Erro ao remover falta: ' . $e->getMessage()
                ], 500);

                // Fechar conexão MySQL mesmo em caso de erro
                DB::disconnect('mysql');

                return $result;
            }
        }

        // Método original por usuario_id e data
        $request->validate([
            'usuario_id' => 'required|exists:usuarios,id',
            'data_falta' => 'required|date'
        ]);

        try {
            if (!Falta::temFalta($request->usuario_id, $request->data_falta)) {
                $result = response()->json([
                    'success' => false,
                    'message' => 'Falta não encontrada para este usuário nesta data!'
                ], 404);

                // Fechar conexão MySQL
                DB::disconnect('mysql');

                return $result;
            }

            Falta::removerFalta($request->usuario_id, $request->data_falta);

            $result = response()->json(['success' => true, 'message' => 'Falta removida com sucesso!']);

            // Fechar conexão MySQL
            DB::disconnect('mysql');

            return $result;
        } catch (\Exception $e) {
            $result = response()->json([
                'success' => false,
                'message' => 'Erro ao remover falta: ' . $e->getMessage()
            ], 500);

            // Fechar conexão MySQL mesmo em caso de erro
            DB::disconnect('mysql');

            return $result;
        }
    }

    public function historico(Request $request)
    {
        // Tratar datas no formato brasileiro
        $dataInicioInput = $request->input('data_inicio');
        $dataFimInput = $request->input('data_fim');

        // Converter datas do formato brasileiro para Y-m-d se necessário
        if ($dataInicioInput && str_contains($dataInicioInput, '/')) {
            $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicioInput)->format('Y-m-d');
        } else {
            $dataInicio = $dataInicioInput ?: now()->startOfMonth()->format('Y-m-d');
        }

        if ($dataFimInput && str_contains($dataFimInput, '/')) {
            $dataFim = Carbon::createFromFormat('d/m/Y', $dataFimInput)->format('Y-m-d');
        } else {
            $dataFim = $dataFimInput ?: now()->format('Y-m-d');
        }

        $usuarioId = $request->input('usuario_id');

        // Buscar faltas no período
        $query = Falta::with('usuario')
            ->where('ativo', 1)
            ->whereBetween('data_falta', [$dataInicio, $dataFim]);

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        $historico = $query->orderBy('data_falta', 'desc')->paginate(25);

        // Dados para filtros
        $usuarios = Usuario::where('situacao_id', 1)->orderBy('nome')->get();

        $result = view('administracao.rh.frequencia.historico', compact(
            'historico',
            'usuarios',
            'dataInicio',
            'dataFim',
            'usuarioId'
        ));

        // Fechar conexão MySQL
        DB::disconnect('mysql');

        return $result;
    }

    public function relatorio(Request $request)
    {
        $mes = $request->input('mes', now()->month);
        $ano = $request->input('ano', now()->year);

        // Buscar usuários ativos
        $usuarios = Usuario::where('situacao_id', 1)->orderBy('nome')->get();

        // Mapear dados de frequência por usuário
        $dadosRelatorio = $usuarios->map(function ($usuario) use ($mes, $ano) {
            $totalFaltas = Falta::contarFaltasMes($usuario->id, $mes, $ano);

            return [
                'usuario' => $usuario,
                'total_faltas' => $totalFaltas,
                'status' => $totalFaltas > 5 ? 'Crítico' : ($totalFaltas > 2 ? 'Atenção' : 'Normal')
            ];
        });

        return view('administracao.rh.frequencia.relatorio', compact(
            'dadosRelatorio',
            'mes',
            'ano'
        ));
    }

    public function relatorioDetalhado(Request $request)
    {
        // Verificar se foi passado um período pré-definido
        $periodo = $request->input('periodo');

        if ($periodo) {
            switch ($periodo) {
                case 'hoje':
                    $dataInicio = now()->format('Y-m-d');
                    $dataFim = now()->format('Y-m-d');
                    break;
                case 'semana':
                    $dataInicio = now()->startOfWeek()->format('Y-m-d');
                    $dataFim = now()->endOfWeek()->format('Y-m-d');
                    break;
                case 'mes':
                    $dataInicio = now()->startOfMonth()->format('Y-m-d');
                    $dataFim = now()->endOfMonth()->format('Y-m-d');
                    break;
                default:
                    // Tratar datas no formato brasileiro
                    $dataInicioInput = $request->input('data_inicio');
                    $dataFimInput = $request->input('data_fim');

                    // Converter datas do formato brasileiro para Y-m-d se necessário
                    if ($dataInicioInput && str_contains($dataInicioInput, '/')) {
                        $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicioInput)->format('Y-m-d');
                    } else {
                        $dataInicio = $dataInicioInput ?: now()->startOfMonth()->format('Y-m-d');
                    }

                    if ($dataFimInput && str_contains($dataFimInput, '/')) {
                        $dataFim = Carbon::createFromFormat('d/m/Y', $dataFimInput)->format('Y-m-d');
                    } else {
                        $dataFim = $dataFimInput ?: now()->format('Y-m-d');
                    }
            }
        } else {
            // Tratar datas no formato brasileiro
            $dataInicioInput = $request->input('data_inicio');
            $dataFimInput = $request->input('data_fim');

            // Converter datas do formato brasileiro para Y-m-d se necessário
            if ($dataInicioInput && str_contains($dataInicioInput, '/')) {
                $dataInicio = Carbon::createFromFormat('d/m/Y', $dataInicioInput)->format('Y-m-d');
            } else {
                $dataInicio = $dataInicioInput ?: now()->startOfMonth()->format('Y-m-d');
            }

            if ($dataFimInput && str_contains($dataFimInput, '/')) {
                $dataFim = Carbon::createFromFormat('d/m/Y', $dataFimInput)->format('Y-m-d');
            } else {
                $dataFim = $dataFimInput ?: now()->format('Y-m-d');
            }
        }

        $usuarioId = $request->input('usuario_id');

        // Buscar faltas no período com detalhes
        $query = Falta::with(['usuario.perfil'])
            ->where('ativo', 1)
            ->whereBetween('data_falta', [$dataInicio, $dataFim]);

        if ($usuarioId) {
            $query->where('usuario_id', $usuarioId);
        }

        $historico = $query->orderBy('data_falta', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(25);

        // Buscar usuários para o filtro
        $usuarios = Usuario::where('situacao_id', 1)->orderBy('nome')->get();

        // Estatísticas do período (consulta separada para obter totais)
        $estatisticasQuery = Falta::where('ativo', 1)
            ->whereBetween('data_falta', [$dataInicio, $dataFim]);

        if ($usuarioId) {
            $estatisticasQuery->where('usuario_id', $usuarioId);
        }

        $totalFaltas = $estatisticasQuery->count();
        $usuariosComFaltas = $estatisticasQuery->distinct('usuario_id')->count();
        $mediaFaltasPorUsuario = $usuariosComFaltas > 0 ? round($totalFaltas / $usuariosComFaltas, 2) : 0;

        return view('administracao.rh.frequencia.relatorio-detalhado', compact(
            'historico',
            'usuarios',
            'dataInicio',
            'dataFim',
            'usuarioId',
            'totalFaltas',
            'usuariosComFaltas',
            'mediaFaltasPorUsuario'
        ));
    }
}
