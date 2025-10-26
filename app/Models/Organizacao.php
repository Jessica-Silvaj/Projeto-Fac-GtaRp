<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Organizacao extends Model
{
    use HasFactory;

    protected $table = 'ORGANIZACAO';
    protected $primatyKey = 'id';
    public static $sequence = "seq_ORGANIZACAO";
    public $timestamps = false;

    protected $fillable = [
        'id',
        'nome',
        'ativo'
    ];

    public static function obterTodos()
    {
        return self::orderBy('nome')->get();
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

        return $query->get();
    }
}
