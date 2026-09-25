<?php
/**
 * Default configuration. Committed to git.
 *
 * Environment-specific values and secrets (database password, app key)
 * go to config.local.php, which is NOT committed and is excluded from
 * deployment sync. See config.local.example.php.
 */

return [
    'app' => [
        'name'     => 'KI-BASE',
        'version'  => '0.1.0',
        'env'      => 'production',     // production | development
        'debug'    => false,            // show error details in the browser
        'timezone' => 'Europe/Warsaw',  // display timezone; the database works in UTC
        'locale'   => 'pl',
    ],

    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => '',
        'user'     => '',
        'password' => '',
    ],

    'security' => [
        // 32 random bytes, base64. Used to encrypt integration tokens.
        'app_key'               => '',
        'force_https'           => true,
        'session_name'          => 'kibase_sid',
        'session_lifetime'      => 60 * 60 * 24 * 7,   // seconds; the session survives browser restarts
        'login_max_attempts'    => 5,                  // failed attempts before lockout
        'login_lockout_minutes' => 15,
    ],
];
