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

require_once __DIR__ . '/../includes/api_helpers.php';

// Vendos header për JSON
header('Content-Type: application/json; charset=utf-8');

// CORS: lejo vetëm origjinën e vetë sajtit, jo çdo domain (*)
$allowedOrigin = defined('SITE_URL') ? SITE_URL : '';
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($requestOrigin !== '' && $requestOrigin === $allowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
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
