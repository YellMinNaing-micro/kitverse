<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo;
    return $pdo ??= new PDO(
        'mysql:host=127.0.0.1;dbname=kitverse_db;charset=utf8mb4',
        getenv('KITVERSE_DB_USER') ?: 'root',
        getenv('KITVERSE_DB_PASS') ?: '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
