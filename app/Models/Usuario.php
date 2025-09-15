<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Usuario extends Model
{
    use  HasFactory;

    protected $table = 'USUARIOS';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_USUARIOS";
    public $timestamps = false;

    protected $fillable =
    [
        'id',
        'nome',
        'senha',
        'matricula',
        'data_admissao',
        'situacao_id',
        'perfil_id'
    ];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id', 'id');
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id', 'id');
    }

    public function funcoes()
    {
        return $this->belongsToMany(Funcao::class, 'USUARIO_FUNCAO', 'usuario_id', 'funcao_id')
            ->using(UsuarioFuncao::class)
            ->withPivot('data_atribuicao');
    }

    public static function realizarLogin($obj)
    {
        // dd(Hash::make($obj['senha']));
        $usuario = self::where("matricula", $obj['matricula'] ?? null)->first();

        if (!$usuario) {
            return null;
        }

        if (!Hash::check($obj['senha'] ?? '', $usuario->senha)) {
            return null;
        }

        // Rehash se necessário (migração transparente de hash mais antigo)
        if (Hash::needsRehash($usuario->senha)) {
            $usuario->senha = Hash::make($obj['senha']);
            $usuario->save();
        }

        return $usuario;
    }

    public static function obterPorMatricula($matricula)
    {
        return self::where('matricula', $matricula)->first();
    }
}
