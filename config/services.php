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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'instagram' => [
        'endpoint' => env('INSTAGRAM_API_ENDPOINT'),
        'token' => env('INSTAGRAM_ACCESS_TOKEN'),
    ],

    'n8n' => [
        'webhook_base_url' => env('N8N_WEBHOOK_BASE_URL'),
        'api_key' => env('N8N_API_KEY'),
        'shared_secret' => env('N8N_SHARED_SECRET'),
        'timeout' => (int) env('N8N_TIMEOUT', 15),
        'allowed_clock_skew' => (int) env('N8N_ALLOWED_CLOCK_SKEW', 300),
    ],

    'ollama' => [
        'base_url' => env('OLLAMA_BASE_URL', 'http://ollama:11434'),
        'model' => env('OLLAMA_MODEL', 'phi3:mini'),
        'timeout' => (int) env('OLLAMA_TIMEOUT', 45),
        'keep_alive' => env('OLLAMA_KEEP_ALIVE', '5m'),
    ],

];
