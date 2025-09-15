<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsuarioFuncao extends Model
{
    use HasFactory;

    protected $table = 'USUARIO_FUNCAO';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable =
    [
        'usuario_id',
        'funcao_id',
        'data_atribuicao'
    ];

    protected $casts = [
        'data_atribuicao' => 'datetime',
    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')
            ->get();
    }
}
