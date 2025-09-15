<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils;
use Illuminate\Support\Facades\Session;

class LogCadastro extends Model
{
    use HasFactory;

    protected $table = 'LOGCADASTRO';
    protected $primatyKey = 'id';
    public static $sequence = 'seq_LOGCADASTRO';
    public $timestamps = false;

    protected $fillable =
    [
        'id',
        'tipo',
        'acao',
        'texto',
        'referencia_id',
        'data',
        'ip',
        'login'
    ];

    public static function inserir($tipo, $acao, $texto, $referencia_id)
    {
        self::create([
            'id' => Utils::getSequence(self::$sequence),
            'tipo' => $tipo,
            'acao' => $acao,
            'texto' => $texto,
            'referencia_id' => $referencia_id,
            'data' => NOW(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'login' => strtoupper(Session::get('nome')),
        ]);
    }
}
