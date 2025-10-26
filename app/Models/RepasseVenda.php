<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepasseVenda extends Model
{
    use HasFactory;

    protected $table = 'repasse_vendas';

    protected $fillable = [
        'repasse_id',
        'fila_espera_id',
        'valor_limpo',
        'valor_sujo'
    ];

    protected $casts = [
        'valor_limpo' => 'decimal:2',
        'valor_sujo' => 'decimal:2'
    ];

    /**
     * Relacionamento com o repasse
     */
    public function repasse()
    {
        return $this->belongsTo(Repasse::class);
    }

    /**
     * Relacionamento com a venda (fila_espera)
     */
    public function venda()
    {
        return $this->belongsTo(FilaEspera::class, 'fila_espera_id');
    }
}
