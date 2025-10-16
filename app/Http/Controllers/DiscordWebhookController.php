<?php

namespace App\Http\Controllers;

use App\Models\DiscordSolicitacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DiscordWebhookController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $tokenConfigurado = config('services.discord.webhook_token');
        $tokenRecebido = $request->header('X-Webhook-Token', $request->input('token'));

        if ($tokenConfigurado && (!is_string($tokenRecebido) || !hash_equals($tokenConfigurado, $tokenRecebido))) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        $payload = $request->all();

        $tipo = Str::upper((string) Arr::get($payload, 'tipo', 'ENTRADA'));
        if (!in_array($tipo, ['ENTRADA', 'SAIDA', 'TRANSFERENCIA'], true)) {
            $tipo = 'ENTRADA';
        }

        $itens = Arr::get($payload, 'itens', Arr::get($payload, 'items', []));
        if (!is_array($itens)) {
            $itens = [];
        }

        $solicitacao = DiscordSolicitacao::create([
            'tipo' => $tipo,
            'status' => DiscordSolicitacao::STATUS_PENDENTE,
            'discord_message_id' => Arr::get($payload, 'message_id'),
            'discord_channel_id' => Arr::get($payload, 'channel_id'),
            'discord_user_id' => Arr::get($payload, 'user_id'),
            'discord_username' => Arr::get($payload, 'username'),
            'bau_origem_id' => Arr::get($payload, 'bau_origem_id'),
            'bau_destino_id' => Arr::get($payload, 'bau_destino_id'),
            'itens' => $itens,
            'payload' => $payload,
            'observacao' => Arr::get($payload, 'observacao') ?? Arr::get($payload, 'content'),
        ]);

        Log::info('Solicitação Discord recebida', [
            'solicitacao_id' => $solicitacao->id,
            'message_id' => $solicitacao->discord_message_id,
        ]);

        return response()->json([
            'message' => 'Solicitação registrada.',
            'id' => $solicitacao->id,
        ], 202);
    }
}
