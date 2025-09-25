<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

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

    public static function obterPorFiltros($request)
    {
        $query = self::orderBy('nome');

        if (!empty($request->nome)) {
            $query = $query->where('nome', 'LIKE', '%' . Str::upper($request->nome) . '%');
        }

        if ($request->filled('ativo')) {
            $query = $query->where('ativo', $request->ativo);
        }

        return  $query->get();
    }
}
