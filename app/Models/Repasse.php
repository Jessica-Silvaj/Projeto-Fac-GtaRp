<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repasse extends Model
{
    use HasFactory;

    protected $table = 'repasses';

    protected $fillable = [
        'vendedor_id',
        'usuario_repasse_id',
        'valor_limpo',
        'valor_sujo',
        'valor_total',
        'observacoes',
        'data_repasse',
        'status'
    ];

    protected $casts = [
        'data_repasse' => 'datetime',
        'valor_limpo' => 'decimal:2',
        'valor_sujo' => 'decimal:2',
        'valor_total' => 'decimal:2'
    ];

    const STATUS_ATIVO = 'ativo';
    const STATUS_DESFEITO = 'desfeito';

    /**
     * Relacionamento com o vendedor (usuário que gerou as vendas)
     */
    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    /**
     * Relacionamento com o usuário que recebeu o repasse
     */
    public function usuarioRepasse()
    {
        return $this->belongsTo(Usuario::class, 'usuario_repasse_id');
    }

    /**
     * Relacionamento com as vendas que foram repassadas
     */
    public function vendas()
    {
        return $this->hasMany(RepasseVenda::class);
    }

    /**
     * Scope para repasses ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('status', self::STATUS_ATIVO);
    }

    /**
     * Scope para repasses desfeitos
     */
    public function scopeDesfeitos($query)
    {
        return $query->where('status', self::STATUS_DESFEITO);
    }
}
