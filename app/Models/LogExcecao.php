<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils;
use Illuminate\Support\Facades\Session;

class LogExcecao extends Model
{
    use HasFactory;

    protected $table = 'LOGEXCECAO';
    protected $primatyKey = 'id';
    public static $sequence = 'seq_LOGEXCECAO';
    public $timestamps = false;

    protected $fillable =
    [
        'excecao',
        'usuario_id',
    ];

    public static function inserirExcessao($excecao)
    {
        $usuario_id = Session::get('usuario_id') ?: null;

        return self::create([
            'id' => Utils::getSequence(self::$sequence),
            'excecao' => $excecao,
            'usuario_id' => $usuario_id,
        ]);
    }
}
