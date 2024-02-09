<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
    use HasFactory;

    protected $table = 'perfil';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_perfil";
    public $timestamps = false;

    protected $fillable =
    [
        'id','nome', 'ativo'
    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')
        ->get();
    }
}
