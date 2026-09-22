<?php
declare(strict_types=1);

// CORS + JSON for all API responses.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Authorization, Content-Type');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Content-Type: application/json; charset=utf-8');
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') { http_response_code(204); exit; }

function respond(bool $success, ?string $message, $data = null, int $code = 200): void {
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}
function ok($data = null, string $message = 'OK'): void { respond(true, $message, $data, 200); }
function fail(string $message, int $code = 400): void { respond(false, $message, null, $code); }

function body(): array {
    $raw = file_get_contents('php://input');
    $j = json_decode($raw ?: '[]', true);
    return is_array($j) ? $j : [];
}

// function image_url(?string $name): ?string {
//     if (!$name) return null;
//     if (str_starts_with($name, 'http')) return $name;
//     // Serve stored uploads/catalog images. Adjust IMAGES_PATH to your web layout.
//     $base = BUYMARK_BASE_URL;
//     return $base . '/uploads/' . ltrim($name, '/');
// }

// Canges

// function image_url(?string $name): ?string
// {
//     if (!$name) {
//         return null;
//     }

//     $base = rtrim(BUYMARK_BASE_URL, '/');
//     $name = ltrim($name, '/');

//     // If database already contains "uploads/..."
//     if (strpos($name, 'uploads/') === 0) {
//         return $base . '/' . $name;
//     }

//     // Otherwise add uploads/
//     return $base . '/uploads/' . $name;
// }

// 2 Canges

function image_url(?string $name): ?string
{
    if (!$name) {
        return null;
    }

    $base = rtrim(BUYMARK_BASE_URL, '/');
    $name = trim($name);

    // Already a complete URL
    if (preg_match('#^https?://#i', $name)) {
        return $name;
    }

    $name = ltrim($name, '/');

    // Old API image path:
    // php_api/api/images/product-1-1.jpg
    // api/images/product-1-1.jpg
    if (preg_match('#^(?:php_api/)?api/images/(.+)$#i', $name, $m)) {
        return $base . '/uploads/products/' . ltrim($m[1], '/');
    }

    // Database already contains uploads/...
    // Example: uploads/shops/shop_1.png
    if (preg_match('#^uploads/(.+)$#i', $name, $m)) {
        return $base . '/uploads/' . ltrim($m[1], '/');
    }

    // Explicit folder paths
    // products/xxx.jpg
    // shops/xxx.jpg
    // categories/xxx.jpg
    if (preg_match('#^(products|shops|categories)/(.+)$#i', $name, $m)) {
        return $base . '/uploads/' . $m[1] . '/' . ltrim($m[2], '/');
    }

    // Category catalog images
    // category-1.jpg, category-2.jpg etc.
    if (preg_match('#^category-\d+.*\.(jpg|jpeg|png|webp)$#i', $name)) {
        return $base . '/uploads/categories/' . $name;
    }

    // Product/catalog images
    // product-1-1.jpg, product-2-1.jpg, showcase-img-7.jpg etc.
    if (preg_match('#^(product-\d+(?:-\d+)?|showcase-img-\d+)\.(jpg|jpeg|png|webp)$#i', $name)) {
        return $base . '/uploads/products/' . $name;
    }

    // Other uploaded files
    return $base . '/uploads/' . $name;
}

// Requires a valid Bearer JWT; returns the user row from user_table.
function require_user(PDO $pdo): array {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '');
    if (!preg_match('/Bearer\s+(.+)/i', $auth, $m)) fail('Please log in to continue.', 401);
    $claims = jwt_verify(trim($m[1]));
    if (!$claims) fail('Session expired. Please log in again.', 401);
    $stmt = $pdo->prepare('SELECT * FROM user_table WHERE user_id = ? LIMIT 1');
    $stmt->execute([(int)$claims['sub']]);
    $u = $stmt->fetch();
    if (!$u) fail('Account not found.', 401);
    return $u;
}

function public_user(array $u): array {
    return [
        'user_id'      => (int)$u['user_id'],
        'username'     => $u['username'],
        'user_email'   => $u['user_email'],
        'user_mobile'  => $u['user_mobile'] ?? null,
        'user_image'   => image_url($u['user_image'] ?? null),
        'user_address' => $u['user_address'] ?? null,
    ];
}

function slugify(string $s): string {
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim($s, '-') ?: 'shop';
}

// ---- Razorpay (server-side only; secrets never sent to the app) ----
define('RZP_KEY_ID', getenv('RAZORPAY_KEY_ID') ?: '');
define('RZP_KEY_SECRET', getenv('RAZORPAY_KEY_SECRET') ?: '');
function razorpay_configured(): bool {
    return RZP_KEY_ID !== '' && RZP_KEY_SECRET !== '' && strpos(RZP_KEY_ID, 'rzp_test_xxx') !== 0;
}
