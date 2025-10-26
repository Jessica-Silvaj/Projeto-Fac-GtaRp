<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilaEsperaItem extends Model
{
    use HasFactory;

    protected $table = 'FILA_ESPERA_ITEM';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public static $sequence = 'seq_FILA_ESPERA_ITEM';

    protected $fillable = [
        'fila_espera_id',
        'produto_id',
        'quantidade',
        'observacao',
        'tabela_preco',
        'preco_unitario_limpo',
        'preco_unitario_sujo',
    ];

    protected $casts = [
        'preco_unitario_limpo' => 'decimal:2',
        'preco_unitario_sujo' => 'decimal:2',
    ];

    public function fila()
    {
        return $this->belongsTo(FilaEspera::class, 'fila_espera_id', 'id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id', 'id');
    }
}
