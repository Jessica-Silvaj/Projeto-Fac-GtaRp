<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Situacao extends Model
{
    use HasFactory;

    protected $table = 'situacao';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_situacao";
    public $timestamps = false;

    protected $fillable =
    [
        'id','nome'
    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')
        ->get();
    }
}
