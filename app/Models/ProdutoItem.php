<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProdutoItem extends Model
{
    use HasFactory;

    protected $table = 'PRODUTO_ITEM';
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable =
    [
        'produto_id',
        'itens_id',
        'quantidade'
    ];
}
