<?php
declare(strict_types=1);

function db_config(): array
{
    $config = [
        'host' => getenv('KITVERSE_DB_HOST') ?: '127.0.0.1',
        'name' => getenv('KITVERSE_DB_NAME') ?: 'kitverse_db',
        'user' => getenv('KITVERSE_DB_USER') ?: 'root',
        'password' => getenv('KITVERSE_DB_PASS') ?: '',
    ];

    $hostname = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '')[0]);
    $localFile = __DIR__ . '/config.local.php';
    if (in_array($hostname, ['kitverse.site.je', 'www.kitverse.site.je'], true) && is_file($localFile)) {
        $local = require $localFile;
        if (!is_array($local)) {
            throw new RuntimeException('config.local.php must return an array.');
        }
        $config = array_replace($config, $local);
    }

    return $config;
}
