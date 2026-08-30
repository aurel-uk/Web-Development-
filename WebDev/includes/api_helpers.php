<?php
/**
 * API HELPERS
 * ===========
 * Funksione të përbashkëta për endpoint-et JSON të API-t.
 * Përdoret nga skedarë API që ekzekutohen si standalone (p.sh. products.php,
 * users.php, orders.php, cart.php) DHE nga api/index.php (router), prandaj
 * çdo funksion mbrohet me function_exists() për të shmangur ridefinimin.
 */

if (!function_exists('jsonResponse')) {
    function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
}

if (!function_exists('getJsonInput')) {
    function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        return $data ?? [];
    }
}

if (!function_exists('requireMethod')) {
    function requireMethod(string $method): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            jsonResponse([
                'success' => false,
                'message' => 'Metoda HTTP e gabuar. Pritej: ' . $method
            ], 405);
        }
    }
}

if (!function_exists('requireAuth')) {
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
}

if (!function_exists('requireAdmin')) {
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
}

if (!function_exists('requireCsrf')) {
    /**
     * Verifikon CSRF token për kërkesa POST/PUT/DELETE nga forma HTML
     * (fusha 'csrf_token' në $_POST) ose nga trupi JSON (çelësi 'csrf_token').
     */
    function requireCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';

        if ($token === '') {
            $json = getJsonInput();
            $token = $json['csrf_token'] ?? '';
        }

        if (!verifyCSRFToken($token)) {
            jsonResponse([
                'success' => false,
                'message' => 'Token CSRF i pavlefshëm ose mungon'
            ], 403);
        }
    }
}

if (!function_exists('clampPerPage')) {
    function clampPerPage(int $perPage, int $max = 100): int
    {
        return max(1, min($perPage, $max));
    }
}
