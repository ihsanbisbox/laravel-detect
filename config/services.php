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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Flask ML API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Flask-based Machine Learning API that handles
    | animal detection using YOLO model for orangutan and wild boar detection.
    |
    */

    'flask' => [
        'url' => env('FLASK_API_URL', 'http://127.0.0.1:5000'),
        'timeout' => env('FLASK_API_TIMEOUT', 60),
        'endpoints' => [
            'detect' => '/detect',
            'health' => '/health',
        ],
        'max_file_size' => env('FLASK_MAX_FILE_SIZE', 10240), // in KB (10MB)
        'supported_formats' => ['jpg', 'jpeg', 'png', 'gif'],
        'confidence_threshold' => env('FLASK_CONFIDENCE_THRESHOLD', 0.5),
    ],

];