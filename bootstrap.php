<?php
declare(strict_types=1);

require_once __DIR__ . '/db-config.php';

function db(): PDO
{
    static $pdo;
    $config = db_config();
    return $pdo ??= new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['name'] . ';charset=utf8mb4',
        $config['user'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
