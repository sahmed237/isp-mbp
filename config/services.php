<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'radius' => [
        'server_ip' => env('RADIUS_SERVER_IP', '192.168.88.10'),
        'auth_port' => (int) env('RADIUS_AUTH_PORT', 1812),
        'acct_port' => (int) env('RADIUS_ACCT_PORT', 1813),
        'coa_port' => (int) env('RADIUS_COA_PORT', 3799),
        'default_nas_ip' => env('RADIUS_DEFAULT_NAS_IP', '192.168.88.1'),
        'tunnel_nas_ip' => env('RADIUS_TUNNEL_NAS_IP', '192.168.88.254'),
        'default_nas_shortname' => env('RADIUS_DEFAULT_NAS_SHORTNAME', 'ISP-MBP-CCR2116-CORE'),
        'default_nas_secret' => env('RADIUS_DEFAULT_NAS_SECRET', 'testing123'),
    ],

    'mikrotik' => [
        'default_host' => env('MIKROTIK_DEFAULT_HOST', '192.168.88.1'),
        'default_username' => env('MIKROTIK_DEFAULT_USERNAME', 'admin'),
        'default_password' => env('MIKROTIK_DEFAULT_PASSWORD', 'password'),
        'default_web_port' => (int) env('MIKROTIK_DEFAULT_WEB_PORT', 80),
        'default_api_port' => (int) env('MIKROTIK_DEFAULT_API_PORT', 8728),
        'default_ssh_port' => (int) env('MIKROTIK_DEFAULT_SSH_PORT', 22),
        'default_mac' => env('MIKROTIK_DEFAULT_MAC', '04:F4:1C:E6:A9:ED'),
    ],

];
