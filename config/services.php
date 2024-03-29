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

    'twitter' => [
        'client_id' => env('TWITTER_CONSUMER_KEY'),
        'client_secret' => env('TWITTER_CONSUMER_SECRET'),
        'consumer_key'    => env('TWITTER_CONSUMER_KEY'),
        'consumer_secret' => env('TWITTER_CONSUMER_SECRET'),
        'access_token'    => env('TWITTER_ACCESS_TOKEN'),
        'access_secret'   => env('TWITTER_ACCESS_SECRET'),
        'redirect' => env('TWITTER_CALLBACK_URL'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_CALLBACK_URL'),
    ],

    'facebook_poster' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'access_token' => env('FACEBOOK_ACCESS_TOKEN'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_CALLBACK_URL'),
        'android' => [
            'assetlinks' => [
                'namespace' => env('ANDROID_ASSETLINKS_NAMESPACE'),
                'package_name' => env('ANDROID_ASSETLINKS_PACKAGE_NAME'),
                'fingerprint' => env('ANDROID_ASSETLINKS_FINGERPRINT'),
            ]
        ],
        'play_store' => [
            'id' => env('PLAY_STORE_ID'),
            'url' => env('PLAY_STORE_URL'),
        ]

    ],

    'compliance' => [
        'iarc_rating_id' => env('IARC_RATING_ID'),
    ],

    'counter' => [
        'tracking_id' => env('COUNTER_TRACKING_ID'),
        'user_id' => env('COUNTER_USER_ID'),
        'access_token' => env('COUNTER_ACCESS_TOKEN'),
    ],
];
