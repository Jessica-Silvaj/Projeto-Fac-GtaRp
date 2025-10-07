<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Permissoes extends Model
{
    use HasFactory;

    protected $table = 'PERMISSAO';
    protected $primatyKey = 'id';
    public static $sequence = "seq_PERMISSAO";
    public $timestamps = false;

    protected $fillable =
    [
        'nome',
        'descricao',
        'ativo'

    ];

    public function funcoes()
    {
        return $this->belongsToMany(Funcao::class, 'PERMISSAO_FUNCAO', 'permissao_id', 'funcao_id')
            ->withPivot('data_atribuicao');
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
            $query = $query->where('nome', 'LIKE', '%' . $request->nome . '%');
        }

        if (!empty($request->descricao)) {
            $query = $query->where('descricao', 'LIKE', '%' . Str::upper($request->descricao) . '%');
        }

        if ($request->filled('ativo')) {
            $query = $query->where('ativo', $request->ativo);
        }

        return  $query->get();
    }
}
