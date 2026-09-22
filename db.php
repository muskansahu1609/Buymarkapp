<?php
// BuyMark REST API — database connection (PDO, prepared statements only).
// Reads credentials from environment or a local .env (never hardcode in the app).
declare(strict_types=1);

$envFile = dirname(__DIR__) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2));
        if (getenv($k) === false) putenv("$k=" . trim($v, "\"'"));
    }
}

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_NAME = getenv('DB_NAME') ?: 'buymark';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASSWORD') ?: '';

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database connection failed.', 'data' => null]);
    exit;
}

// Public base URL used to build absolute image URLs returned to the app.
// define('BUYMARK_BASE_URL', rtrim(getenv('APP_BASE_URL') ?: '', '/'));
define('BUYMARK_BASE_URL', rtrim(getenv('APP_BASE_URL') ?: 'http://10.137.158.154/buymark68', '/'));
