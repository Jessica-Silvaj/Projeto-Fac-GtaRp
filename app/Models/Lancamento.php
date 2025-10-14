<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    use HasFactory;

    protected $table = 'LANCAMENTO';
    protected $primaryKey = 'id';
    public static $sequence = 'seq_LANCAMENTO';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'itens_id',
        'tipo',
        'quantidade',
        'usuario_id',
        'bau_origem_id',
        'bau_destino_id',
        'observacao',
        'data_atribuicao',
    ];

    public function item()
    {
        return $this->belongsTo(Itens::class, 'itens_id');
    }

    public function bauOrigem()
    {
        return $this->belongsTo(Baus::class, 'bau_origem_id');
    }

    public function bauDestino()
    {
        return $this->belongsTo(Baus::class, 'bau_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }
}

