<?php
require_once 'vendor/autoload.php';
require_once './class-db.php';

define('GOOGLE_CLIENT_ID', '805936763306-17eqcckj9ptj2bad9t2d4t6r40jdranp.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');

$config = [
    'callback' => 'http://localhost/SyncGoogleSheet/callback.php',
    'keys'     => [
        'id' => GOOGLE_CLIENT_ID,
        'secret' => GOOGLE_CLIENT_SECRET
    ],
    'scope'    => 'https://www.googleapis.com/auth/spreadsheets',
    'authorize_url_parameters' => [
        'approval_prompt' => 'force', // to pass only when you need to acquire a new refresh token.
        'access_type' => 'offline'
    ]
];

$adapter = new Hybridauth\Provider\Google( $config );
