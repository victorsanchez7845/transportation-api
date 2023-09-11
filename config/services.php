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
    'maps' => [
        'key' => env('GOOGLE_MAPS_API'),
    ],
    'flights' => [
        'key' => env('FLIGHT_TRACKING'),
    ],
    'mailjet' => [
        'key' => env('MAILJET_KEY'),
        'secret' => env('MAILJET_SECRET'),
    ],
    'jwt' => [
        'key' => env('JWT_TOKEN'),
    ],
    'airbrake' => [
        'id' => env('AIRBRAKE_ID'),
        'key' => env('AIRBRAKE_KEY'),
    ],
    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
    'paypal' => [
        'merchant' => env('PAYPAL_MERCHANT_ID'),
    ],
];
