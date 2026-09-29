<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../bootstrap.php';

$email = trim((string) readline('Admin email: '));
$password = trim((string) readline('New password (8+ characters): '));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    fwrite(STDERR, "Enter a valid email and password of at least 8 characters.\n");
    exit(1);
}
$statement = db()->prepare('INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE password=VALUES(password),role="admin",status=1');
$statement->execute(['KitVerse Admin', $email, password_hash($password, PASSWORD_DEFAULT), 'admin']);
echo "Admin account ready.\n";
