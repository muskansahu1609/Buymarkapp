<?php
// Minimal self-contained JWT (HS256) — no external composer dependency required.
declare(strict_types=1);

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'change-me-in-production-min-32-chars');
define('JWT_TTL_DAYS', 30);

function b64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}
function b64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}

function jwt_issue(int $userId, string $email): string {
    $header  = ['alg' => 'HS256', 'typ' => 'JWT'];
    $now     = time();
    $payload = [
        'sub'   => (string)$userId,
        'email' => $email,
        'iat'   => $now,
        'exp'   => $now + (JWT_TTL_DAYS * 86400),
    ];
    $h = b64url_encode(json_encode($header));
    $p = b64url_encode(json_encode($payload));
    $sig = b64url_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    return "$h.$p.$sig";
}

function jwt_verify(string $token): ?array {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;
    [$h, $p, $sig] = $parts;
    $expected = b64url_encode(hash_hmac('sha256', "$h.$p", JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) return null;
    $payload = json_decode(b64url_decode($p), true);
    if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) return null;
    return $payload;
}
