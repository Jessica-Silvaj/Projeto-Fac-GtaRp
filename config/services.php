<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'discord' => [
        'bot_token' => env('DISCORD_BOT_TOKEN'),
        'canal_entrada_id' => env('CANAL_ENTRADA_ID'),
        'canal_saida_id' => env('CANAL_SAIDA_ID'),
        'canal_aprovada_id' => env('CANAL_MENSAGEM_APROVADA'),
        'canal_reprovada_id' => env('CANAL_MENSAGEM_REPROVADA'),
        'webhook_token' => env('DISCORD_WEBHOOK_TOKEN'),
        'webhook_entradas' => env('DISCORD_WEBHOOK_ENTRADAS'),
        'webhook_saidas' => env('DISCORD_WEBHOOK_SAIDAS'),
    ],

];
