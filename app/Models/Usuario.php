<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use  HasFactory;

    protected $table = 'usuarios';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_usuario";
    public $timestamps = false;

    protected $fillable =
    [
        'id','nome', 'senha', 'matricula', 'data_admissao','situacao_id', 'perfil_id'
    ];

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'perfil_id', 'id');
    }

    public function situacao()
    {
        return $this->belongsTo(Situacao::class, 'situacao_id', 'id');
    }


    public static function realizarLogin($obj)
    {
        // dd(crypt($obj['senha'], 'a45zzzz2s'));
        $usuario = self::where("matricula",$obj['matricula'])->first();
        if($usuario && $usuario->senha == crypt($obj['senha'], 'a45zzzz2s')){
            //TODO CRIAR VALIDAÇÃO DE PERFIL VAZIOS
            if(empty($usuario->perfil_id)){

            }
            return $usuario;
        }

        return null;
    }

    public static function obterPorMatricula($matricula){
       return self::where('matricula', $matricula)->first();
    }

}
