<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Usuario extends Authenticatable
{
    use  HasFactory;

    protected $table = 'USUARIOS';
    protected $primatyKey = 'id';
    protected static $sequence = "seq_USUARIOS";
    public $timestamps = false;

    protected $fillable =
    [
        'nome',
        'senha',
        'matricula',
        'data_admissao',
        'situacao_id',
        'perfil_id'
    ];

    protected $hidden = [
        'senha',
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
            ->withPivot('data_atribuicao');
    }

    public static function realizarLogin($obj)
    {
        $usuario = self::where("matricula", $obj['matricula'] ?? null)->first();
        if (!$usuario) {
            return null;
        }

        $senhaInformada = $obj['senha'] ?? '';


        // Cria o hash com o mesmo salt usado no cadastro
        $senhaCriptografada = crypt($senhaInformada, 'a45zzzz2s');
        // dd($senhaCriptografada);
        // a46PaBaWc0NC2 = 123456
        // Compara diretamente com o valor salvo no banco
        if ($usuario->senha !== $senhaCriptografada) {
            return null;
        }

        return $usuario;
    }

    public static function obterPorMatricula($matricula)
    {
        return self::where('matricula', $matricula)->first();
    }

    public static function obterPorFiltros($request)
    {
        $funcoesFiltro = collect($request->funcoes ?? [])
            ->filter(fn($v) => $v !== '' && $v !== null)
            ->map(fn($v) => (int) $v)
            ->values()
            ->all();

        $filtraPorFuncao = !empty($funcoesFiltro);

        $query = self::with([
            'situacao',
            'perfil',
            'funcoes',
        ])->orderBy('nome');

        if ($filtraPorFuncao) {
            $query->whereHas('funcoes', function ($q) use ($funcoesFiltro) {
                $q->whereIn('FUNCAO.id', $funcoesFiltro);
            });
        }

        if (!empty($request->nome)) {
            $query = $query->where('nome', 'LIKE', '%' . Str::upper($request->nome) . '%')
                ->orWhere('matricula', $request->nome);
        }

        if (!empty($request->situacao)) {
            $query = $query->where('situacao_id', $request->situacao);
        }

        if (!empty($request->perfil)) {
            $query = $query->where('perfil_id', $request->perfil);
        }
        return  $query->get();
    }

    public function hasPermissao($permissao)
    {
        if (empty($permissao)) {
            return false;
        }

        $query = DB::table('PERMISSAO as p')
            ->join('PERMISSAO_FUNCAO as pf', 'p.id', '=', 'pf.permissao_id')
            ->join('USUARIO_FUNCAO as uf', 'pf.funcao_id', '=', 'uf.funcao_id')
            ->where('uf.usuario_id', $this->id)
            ->where('p.ativo', 1);

        if (is_array($permissao)) {
            $query->whereIn('p.nome', $permissao);
        } else {
            $query->where('p.nome', $permissao);
        }

        return $query->exists();
    }
}
