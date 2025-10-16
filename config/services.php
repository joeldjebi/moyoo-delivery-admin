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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY'), // Legacy (déprécié)
        'project_id' => env('FIREBASE_PROJECT_ID', 'moyoo-fleet'),
        'web_api_key' => env('FIREBASE_WEB_API_KEY', 'AIzaSyAWezTKUpu9trZW1gb2PnKVqKX4r4aUTWI'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID', '319265524393'),
        'app_id' => env('FIREBASE_APP_ID', '1:319265524393:web:e393bd81b802e15fb277b8'),
        'service_account_key' => [
            'type' => env('FIREBASE_SA_TYPE', 'service_account'),
            'project_id' => env('FIREBASE_SA_PROJECT_ID', 'moyoo-fleet'),
            'private_key_id' => env('FIREBASE_SA_PRIVATE_KEY_ID'),
            'private_key' => env('FIREBASE_SA_PRIVATE_KEY'),
            'client_email' => env('FIREBASE_SA_CLIENT_EMAIL'),
            'client_id' => env('FIREBASE_SA_CLIENT_ID'),
            'auth_uri' => env('FIREBASE_SA_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth'),
            'token_uri' => env('FIREBASE_SA_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
            'auth_provider_x509_cert_url' => env('FIREBASE_SA_AUTH_PROVIDER_CERT_URL', 'https://www.googleapis.com/oauth2/v1/certs'),
            'client_x509_cert_url' => env('FIREBASE_SA_CLIENT_CERT_URL'),
        ],
    ],

];
