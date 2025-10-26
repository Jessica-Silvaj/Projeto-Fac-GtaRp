<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Falta extends Model
{
    use HasFactory;

    protected $table = 'FALTAS';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'data_falta',
        'motivo',
        'ativo'
    ];

    protected $casts = [
        'data_falta' => 'date',
        'ativo' => 'boolean'
    ];

    // Relacionamentos
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    // Métodos auxiliares
    public static function temFalta($usuarioId, $data)
    {
        return self::where('usuario_id', $usuarioId)
            ->where('data_falta', $data)
            ->where('ativo', 1)
            ->exists();
    }

    public static function contarFaltasMes($usuarioId, $mes = null, $ano = null)
    {
        $query = self::where('usuario_id', $usuarioId)
            ->where('ativo', 1);

        if ($mes && $ano) {
            $query->whereYear('data_falta', $ano)
                ->whereMonth('data_falta', $mes);
        } else {
            // Último mês por padrão
            $dataInicio = now()->subMonth()->startOfMonth();
            $dataFim = now()->endOfMonth();
            $query->whereBetween('data_falta', [$dataInicio, $dataFim]);
        }

        return $query->count();
    }

    public static function registrarFalta($usuarioId, $data, $motivo)
    {
        return self::create([
            'usuario_id' => $usuarioId,
            'data_falta' => $data,
            'motivo' => $motivo,
            'ativo' => 1
        ]);
    }

    public static function removerFalta($usuarioId, $data)
    {
        return self::where('usuario_id', $usuarioId)
            ->where('data_falta', $data)
            ->where('ativo', 1)
            ->delete();
    }
}
