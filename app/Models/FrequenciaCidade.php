<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class FrequenciaCidade extends Model
{
    use HasFactory;

    protected $table = 'FREQUENCIA_CIDADE';
    public $timestamps = true;

    // Status disponíveis
    const STATUS_PRESENTE = 'presente';
    const STATUS_AUSENTE = 'ausente';
    const STATUS_JUSTIFICADO = 'justificado';

    protected $fillable = [
        'usuario_id',
        'data_entrada',
        'horario_entrada',
        'horario_saida',
        'observacoes',
        'status',
        'registrado_por'
    ];

    protected $casts = [
        'data_entrada' => 'date',
        'horario_entrada' => 'datetime:H:i',
        'horario_saida' => 'datetime:H:i',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function registradoPor()
    {
        return $this->belongsTo(Usuario::class, 'registrado_por', 'id');
    }

    // Scopes
    public function scopePorPeriodo($query, $dataInicio, $dataFim)
    {
        return $query->whereBetween('data_entrada', [$dataInicio, $dataFim]);
    }

    public function scopePresente($query)
    {
        return $query->where('status', self::STATUS_PRESENTE);
    }

    public function scopeAusente($query)
    {
        return $query->where('status', self::STATUS_AUSENTE);
    }

    public function scopeJustificado($query)
    {
        return $query->where('status', self::STATUS_JUSTIFICADO);
    }

    // Métodos auxiliares
    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case self::STATUS_PRESENTE:
                return ['classe' => 'success', 'texto' => 'Presente'];
            case self::STATUS_AUSENTE:
                return ['classe' => 'danger', 'texto' => 'Ausente'];
            case self::STATUS_JUSTIFICADO:
                return ['classe' => 'warning', 'texto' => 'Justificado'];
            default:
                return ['classe' => 'secondary', 'texto' => 'Indefinido'];
        }
    }

    public function getTempoNaCidadeAttribute()
    {
        if (!$this->horario_entrada) {
            return null;
        }

        $entrada = Carbon::parse($this->data_entrada . ' ' . $this->horario_entrada);
        $saida = $this->horario_saida ?
            Carbon::parse($this->data_entrada . ' ' . $this->horario_saida) :
            now();

        return $entrada->diff($saida);
    }

    public function getTempoFormatadoAttribute()
    {
        $tempo = $this->tempo_na_cidade;

        if (!$tempo) {
            return 'N/A';
        }

        $horas = $tempo->h;
        $minutos = $tempo->i;

        if ($horas > 0) {
            return $horas . 'h' . ($minutos > 0 ? ' ' . $minutos . 'min' : '');
        }

        return $minutos . 'min';
    }

    // Métodos estáticos
    public static function registrarPresenca($usuarioId, $data, $dados = [])
    {
        // Verificar se já existe registro para o dia
        $registroExistente = self::where('usuario_id', $usuarioId)
            ->where('data_entrada', $data)
            ->first();

        if ($registroExistente && $registroExistente->status === self::STATUS_PRESENTE) {
            throw new \Exception('Já existe um registro de presença para este usuário nesta data.');
        }

        return self::updateOrCreate(
            [
                'usuario_id' => $usuarioId,
                'data_entrada' => $data
            ],
            array_merge([
                'status' => self::STATUS_PRESENTE,
                'horario_entrada' => now()->format('H:i:s'),
                'registrado_por' => auth()->id()
            ], $dados)
        );
    }

    public static function podeMarcarPresenca($usuarioId, $data)
    {
        $registro = self::where('usuario_id', $usuarioId)
            ->where('data_entrada', $data)
            ->first();

        return !$registro || $registro->status !== self::STATUS_PRESENTE;
    }

    public static function getEstatisticasGerais($dataInicio, $dataFim)
    {
        $totalRegistros = self::whereBetween('data_entrada', [$dataInicio, $dataFim])->count();
        $totalPresencas = self::whereBetween('data_entrada', [$dataInicio, $dataFim])
            ->where('status', self::STATUS_PRESENTE)
            ->count();
        $totalAusencias = self::whereBetween('data_entrada', [$dataInicio, $dataFim])
            ->where('status', self::STATUS_AUSENTE)
            ->count();

        return [
            'total_registros' => $totalRegistros,
            'total_presencas' => $totalPresencas,
            'total_ausencias' => $totalAusencias,
            'percentual_presenca' => $totalRegistros > 0 ? round(($totalPresencas / $totalRegistros) * 100, 1) : 0
        ];
    }

    public static function marcarSaida($usuarioId, $data, $horarioSaida = null)
    {
        $registro = self::where('usuario_id', $usuarioId)
            ->where('data_entrada', $data)
            ->first();

        if ($registro) {
            $registro->update([
                'horario_saida' => $horarioSaida ?: now()->format('H:i:s')
            ]);
        }

        return $registro;
    }

    public static function getFrequenciaPorPeriodo($usuarioId, $dataInicio, $dataFim)
    {
        $totalDias = Carbon::parse($dataInicio)->diffInDays(Carbon::parse($dataFim)) + 1;

        $diasPresente = self::where('usuario_id', $usuarioId)
            ->porPeriodo($dataInicio, $dataFim)
            ->whereIn('status', [self::STATUS_PRESENTE, self::STATUS_JUSTIFICADO])
            ->count();

        $diasAusente = self::where('usuario_id', $usuarioId)
            ->porPeriodo($dataInicio, $dataFim)
            ->where('status', self::STATUS_AUSENTE)
            ->count();

        $diasSemRegistro = $totalDias - $diasPresente - $diasAusente;

        return [
            'total_dias' => $totalDias,
            'dias_presente' => $diasPresente,
            'dias_ausente' => $diasAusente,
            'dias_sem_registro' => $diasSemRegistro,
            'percentual_frequencia' => $totalDias > 0 ? round(($diasPresente / $totalDias) * 100, 1) : 0
        ];
    }
}
