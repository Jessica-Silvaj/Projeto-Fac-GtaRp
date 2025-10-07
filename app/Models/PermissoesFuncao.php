<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissoesFuncao extends Model
{
    use HasFactory;

    protected $table = 'PERMISSAO_FUNCAO';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable =
    [
        'permissao_id',
        'funcao_id',
        'data_atribuicao'
    ];

    protected $casts = [
        'data_atribuicao' => 'datetime:Y-m-d H:i:s',
    ];
}
