<?php

namespace App\Models;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordSolicitacao extends Model
{
    use HasFactory;

    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_APROVADA = 'aprovada';
    public const STATUS_REJEITADA = 'rejeitada';
    public const STATUS_AJUSTE = 'ajuste';

    protected $table = 'DISCORD_SOLICITACAO';

    protected $fillable = [
        'tipo',
        'status',
        'discord_message_id',
        'discord_channel_id',
        'discord_user_id',
        'discord_username',
        'bau_origem_id',
        'bau_destino_id',
        'itens',
        'payload',
        'observacao',
        'processado_em',
        'processado_por',
        'lancamentos_ids',
    ];

    protected $casts = [
        'itens' => 'array',
        'payload' => 'array',
        'lancamentos_ids' => 'array',
        'processado_em' => 'datetime',
    ];

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'processado_por');
    }
}
