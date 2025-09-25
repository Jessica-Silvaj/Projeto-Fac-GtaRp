<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Funcao extends Model
{
    use HasFactory;

    protected $table = 'FUNCAO';
    protected $primatyKey = 'id';
    public static $sequence = 'seq_FUNCAO';
    public $timestamps = false;

    protected $fillable =
    [
        'nome',
        'ativo'
    ];

    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'USUARIO_FUNCAO', 'funcao_id', 'usuario_id')
            ->using(UsuarioFuncao::class)
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
            $query = $query->where('nome', 'LIKE', '%' . Str::upper($request->nome) . '%');
        }

        if ($request->filled('ativo')) {
            $query = $query->where('ativo', $request->ativo);
        }

        return  $query->get();
    }
}
