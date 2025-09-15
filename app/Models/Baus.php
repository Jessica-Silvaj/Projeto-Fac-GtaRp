<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Baus extends Model
{
    use HasFactory;

    protected $table = 'BAUS';
    protected $primatyKey = 'id';
    public static $sequence = 'seq_BAUS';
    public $timestamps = false;

    protected $fillable =
    [
        'id',
        'nome',
        'ativo'
    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')
            ->get();
    }
}
