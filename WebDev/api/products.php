<?php
/**
 * API ENDPOINTS - PRODUKTET
 * =========================
 * Endpoint standalone (jo vetëm përmes routerit api/index.php) që përdoret
 * drejtpërdrejt nga paneli i adminit (admin/products.php -> POST /api/products.php).
 *
 * Veprime (action nga $_GET/$_POST):
 * - GET  ?action=list       - Lista e produkteve
 * - GET  ?action=single&id= - Detajet e një produkti
 * - GET  ?action=categories - Lista e kategorive
 * - POST action=delete      - Fshi produkt (admin)
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json; charset=utf-8');

$jsonBody = getJsonInput();
$action = $_GET['action'] ?? $_POST['action'] ?? $jsonBody['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$product = new Product();

switch ($action) {
    // ============================================
    // GET ALL PRODUCTS
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');

        $page = (int)($_GET['page'] ?? 1);
        $perPage = clampPerPage((int)($_GET['per_page'] ?? 12));

        $filters = [
            'category_id' => $_GET['category'] ?? null,
            'search' => $_GET['search'] ?? '',
            'min_price' => $_GET['min_price'] ?? null,
            'max_price' => $_GET['max_price'] ?? null,
            'sort' => $_GET['sort'] ?? 'newest'
        ];

        $result = $product->getAllProducts($page, $perPage, $filters);

        jsonResponse([
            'success' => true,
            'data' => $result['products'],
            'pagination' => [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'total_items' => $result['total'],
                'per_page' => $perPage
            ]
        ]);
        break;

    // ============================================
    // GET SINGLE PRODUCT
    // ============================================
    case 'single':
        requireMethod('GET');

        $productId = (int)($_GET['id'] ?? 0);
        $productData = $productId ? $product->getProduct($productId) : null;

        if ($productData) {
            jsonResponse([
                'success' => true,
                'data' => $productData
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Produkti nuk u gjet'
            ], 404);
        }
        break;

    // ============================================
    // DELETE PRODUCT (Admin only)
    // ============================================
    case 'delete':
        // Formularët HTML nuk dërgojnë dot metodën DELETE, prandaj pranojmë POST
        if (!in_array($method, ['POST', 'DELETE'], true)) {
            jsonResponse([
                'success' => false,
                'message' => 'Metoda HTTP e gabuar. Pritej: POST ose DELETE'
            ], 405);
        }

        requireAdmin();
        requireCsrf();

        $productId = (int)($_POST['product_id'] ?? $_GET['id'] ?? 0);
        if (!$productId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e produktit mungon'
            ], 400);
        }

        $result = $product->deleteProduct($productId);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message']
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        break;

    // ============================================
    // GET CATEGORIES
    // ============================================
    case 'categories':
        requireMethod('GET');

        jsonResponse([
            'success' => true,
            'data' => $product->getAllCategories()
        ]);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
