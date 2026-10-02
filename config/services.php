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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'payhere' => [
        'enabled' => env('PAYHERE_ENABLED', false),
        'sandbox' => env('PAYHERE_SANDBOX', true),
        'merchant_id' => env('PAYHERE_MERCHANT_ID'),
        'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),
        'currency' => env('PAYHERE_CURRENCY', 'LKR'),
        'checkout_url' => env('PAYHERE_CHECKOUT_URL', env('PAYHERE_SANDBOX', true)
            ? 'https://sandbox.payhere.lk/pay/checkout'
            : 'https://www.payhere.lk/pay/checkout'),
    ],

    'ai_assistant' => [
        'provider' => env('AI_ASSISTANT_PROVIDER', env('OPENAI_API_KEY') ? 'openai' : 'local'),
        'endpoint' => env('AI_ASSISTANT_ENDPOINT', 'https://api.openai.com/v1/responses'),
        'api_key' => env('AI_ASSISTANT_API_KEY', env('OPENAI_API_KEY')),
        'model' => env('AI_ASSISTANT_MODEL', env('OPENAI_MODEL', 'gpt-4o-mini')),
    ],

    'maps' => [
        'provider' => env('MAPS_PROVIDER', env('MAPS_API_KEY') ? 'google' : 'local'),
        'api_key' => env('MAPS_API_KEY'),
        'region' => env('MAPS_REGION', 'lk'),
        'osrm_enabled' => env('MAPS_OSRM_ENABLED', true),
        'nominatim_enabled' => env('MAPS_NOMINATIM_ENABLED', true),
    ],

    'contact' => [
        'inbox' => env('CONTACT_INBOX_EMAIL', env('MAIL_FROM_ADDRESS', 'info@villagelink.lk')),
    ],

    'password_reset' => [
        'url' => env('PASSWORD_RESET_URL', env('APP_URL', 'http://localhost')),
    ],

];
