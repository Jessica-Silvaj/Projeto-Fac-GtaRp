<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BAUS extends Model
{
    use HasFactory;

    protected $table = 'BAUS';
    protected $primatyKey = 'id';
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
