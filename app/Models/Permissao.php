<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PERMISSAO extends Model
{
    use HasFactory;

    protected $table = 'PERMISSAO';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_PERMISSAO";
    public $timestamps = false;

    protected $fillable =
    [
        'nome',
        'descricao',
        'ativo'

    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')
            ->get();
    }
}
