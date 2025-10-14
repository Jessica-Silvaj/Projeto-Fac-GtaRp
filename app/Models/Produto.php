<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'PRODUTO';
    protected $primaryKey = 'id';
    public static $sequence = 'seq_PRODUTO';
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'quantidade',
        'ativo',
    ];

    public function itens()
    {
        return $this->belongsToMany(Itens::class, 'PRODUTO_ITEM', 'produto_id', 'itens_id')
            ->withPivot('quantidade');
    }

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
