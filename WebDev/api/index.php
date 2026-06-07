<?php
/**
 * API ROUTER
 * ==========
 * Ky skedar menaxhon të gjitha kërkesat API.
 * Përcakton rutën dhe dërgon te skedari përkatës.
 *
 * Përdorimi:
 * - GET /api/products - Merr produktet
 * - POST /api/auth/login - Login
 * - etj.
 */

// Inicializo
require_once __DIR__ . '/../includes/init.php';

// Vendos header për JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

/**
 * Funksion për të dërguar përgjigje JSON
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit();
}

/**
 * Funksion për të marrë të dhënat JSON nga request body
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return $data ?? [];
}

/**
 * Funksion për të verifikuar metodën HTTP
 */
function requireMethod(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        jsonResponse([
            'success' => false,
            'message' => 'Metoda HTTP e gabuar. Pritej: ' . $method
        ], 405);
    }
}

/**
 * Funksion për të verifikuar autentikimin
 */
function requireAuth(): int
{
    if (!isLoggedIn()) {
        jsonResponse([
            'success' => false,
            'message' => 'Duhet të jeni i loguar'
        ], 401);
    }
    return getCurrentUserId();
}

/**
 * Funksion për të verifikuar rolin admin
 */
function requireAdmin(): void
{
    requireAuth();
    if (!isAdmin()) {
        jsonResponse([
            'success' => false,
            'message' => 'Akses i ndaluar'
        ], 403);
    }
}

// Merr rutën nga URL
$requestUri = $_SERVER['REQUEST_URI'];
$basePath = '/WebDev/api';

// Hiq query string dhe base path
$path = parse_url($requestUri, PHP_URL_PATH);
$path = str_replace($basePath, '', $path);
$path = trim($path, '/');

// Ndaj rutën në pjesë
$parts = explode('/', $path);
$endpoint = $parts[0] ?? '';
$action = $parts[1] ?? '';
$id = $parts[2] ?? null;

// Route te endpoint-i përkatës
switch ($endpoint) {
    case 'auth':
        require_once __DIR__ . '/auth.php';
        break;

    case 'products':
        require_once __DIR__ . '/products.php';
        break;

    case 'cart':
        require_once __DIR__ . '/cart.php';
        break;

    case 'users':
        require_once __DIR__ . '/users.php';
        break;

    case 'orders':
        require_once __DIR__ . '/orders.php';
        break;

    case '':
        // API Info
        jsonResponse([
            'success' => true,
            'message' => 'WebDev API v1.0',
            'endpoints' => [
                'auth' => '/api/auth - Autentikimi',
                'products' => '/api/products - Produktet',
                'cart' => '/api/cart - Shporta',
                'users' => '/api/users - Përdoruesit',
                'orders' => '/api/orders - Porositë'
            ]
        ]);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Endpoint i panjohur: ' . $endpoint
        ], 404);
}
