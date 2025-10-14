<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Itens extends Model
{
    use HasFactory;

    protected $table = 'ITENS';
    protected $primaryKey = 'id';
    public static $sequence = 'seq_ITENS';
    public $timestamps = false;

    protected $fillable =
    [
        'id',
        'nome',
        'ativo'
    ];

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'PRODUTO_ITEM', 'itens_id', 'produto_id')
            ->withPivot('quantidade');
    }

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
