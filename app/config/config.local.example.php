<?php
/**
 * Copy this file to config.local.php and fill in the values.
 * config.local.php is never committed and never overwritten by deploy.
 *
 * App key: generate once, e.g. in Git Bash:
 *     openssl rand -base64 32
 * Keep it in the password manager: without it encrypted integration
 * tokens cannot be read.
 */

return [
    'app' => [
        'env'   => 'production',
        'debug' => false,
    ],

    'db' => [
        'host'     => 'localhost',
        'name'     => 'alexivan_ki_base',
        'user'     => 'alexivan_',
        'password' => '',
    ],

    'security' => [
        'app_key'     => '',
        'force_https' => true,   // set to false only for a local development server without HTTPS
    ],
];
