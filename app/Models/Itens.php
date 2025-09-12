<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ITENS extends Model
{
    use HasFactory;

    protected $table = 'ITENS';
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

    public static function obterPorFiltros($request)
    {
        $query = self::orderBy('nome');

        if(!empty($request->nome)) {
            $query = $query->where('nome', 'LIKE', '%'.Str::upper($request->nome).'%');
        }

        if ($request->filled('ativo')) {
            $query = $query->where('ativo', $request->ativo);
        }

        return  $query->get();
    }
}
