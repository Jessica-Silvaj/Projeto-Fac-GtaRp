<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FilaEspera extends Model
{
    use HasFactory;

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_EM_ATENDIMENTO = 'em_atendimento';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_CANCELADO = 'cancelado';

    protected $table = 'FILA_ESPERA';
    protected $primaryKey = 'id';
    public $timestamps = false;
    public static $sequence = 'seq_FILA_ESPERA';

    protected $fillable = [
        'id',
        'organizacao_id',
        'nome',
        'data_pedido',
        'data_entrega_estimada',
        'usuario_id',
        'pedido',
        'status',
        'dinheiro_limpo',
        'dinheiro_sujo',
        'desconto_aplicado',
        'desconto_valor',
        'desconto_motivo',
        'pagamento_tipo',
    ];

    protected $casts = [
        'data_pedido' => 'datetime',
        'data_entrega_estimada' => 'datetime',
        'status' => 'string',
        'dinheiro_limpo' => 'decimal:2',
        'dinheiro_sujo' => 'decimal:2',
        'desconto_aplicado' => 'boolean',
        'desconto_valor' => 'decimal:2',
        'pagamento_tipo' => 'string',
    ];

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class, 'organizacao_id', 'id');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    public function itens()
    {
        return $this->hasMany(FilaEsperaItem::class, 'fila_espera_id', 'id')->with('produto');
    }

    public static function queryComFiltros($request, bool $ignorarStatus = false)
    {
        $query = self::with(['organizacao', 'usuario'])
            ->orderByDesc('data_pedido')
            ->orderByDesc('id');

        if ($request->filled('organizacao_id')) {
            $query->where('organizacao_id', (int) $request->input('organizacao_id'));
        }

        if ($request->filled('organizacao')) {
            $valor = Str::upper($request->input('organizacao'));

            $query->where(function ($q) use ($valor) {
                $q->whereHas('organizacao', function ($sub) use ($valor) {
                    $sub->where('nome', 'LIKE', '%' . $valor . '%');
                })->orWhere('nome', 'LIKE', '%' . $valor . '%');
            });
        }

        if ($request->filled('pessoa')) {
            $query->where('nome', 'LIKE', '%' . Str::upper($request->input('pessoa')) . '%');
        }

        if ($request->filled('responsavel')) {
            $query->where('usuario_id', (int) $request->input('responsavel'));
        }

        $statusFiltro = $request->input('status');

        if (!$ignorarStatus && !empty($statusFiltro) && $statusFiltro !== 'todos') {
            $query->where('status', $statusFiltro);
        }

        if ($request->filled('data_pedido_de')) {
            $query->whereDate('data_pedido', '>=', $request->input('data_pedido_de'));
        }

        if ($request->filled('data_pedido_ate')) {
            $query->whereDate('data_pedido', '<=', $request->input('data_pedido_ate'));
        }

        if ($request->filled('data_entrega_de')) {
            $query->whereDate('data_entrega_estimada', '>=', $request->input('data_entrega_de'));
        }

        if ($request->filled('data_entrega_ate')) {
            $query->whereDate('data_entrega_estimada', '<=', $request->input('data_entrega_ate'));
        }

        return $query;
    }

    public static function obterPorFiltros($request)
    {
        return self::queryComFiltros($request)->get();
    }
}
