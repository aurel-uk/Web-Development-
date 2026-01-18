<?php
/**
 * API ENDPOINTS - PRODUKTET
 * =========================
 * Menaxhon CRUD për produktet.
 *
 * Endpoints:
 * - GET /api/products - Lista e produkteve
 * - GET /api/products/{id} - Detajet e produktit
 * - POST /api/products - Shto produkt (admin)
 * - PUT /api/products/{id} - Përditëso produkt (admin)
 * - DELETE /api/products/{id} - Fshi produkt (admin)
 * - GET /api/products/categories - Lista e kategorive
 */

$product = new Product();
$method = $_SERVER['REQUEST_METHOD'];

// Nëse action është numër, atëherë është ID
if (is_numeric($action)) {
    $id = (int)$action;
    $action = 'single';
}

switch ($action) {
    // ============================================
    // GET ALL PRODUCTS
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 12);
        $category = $_GET['category'] ?? null;
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'newest';
        $minPrice = $_GET['min_price'] ?? null;
        $maxPrice = $_GET['max_price'] ?? null;

        $filters = [
            'category_id' => $category,
            'search' => $search,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort
        ];

        $result = $product->getAll($page, $perPage, $filters);

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

        $productData = $product->getById($id);

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
    // CREATE PRODUCT (Admin only)
    // ============================================
    case 'create':
        requireMethod('POST');
        requireAdmin();

        $data = getJsonInput();
        $result = $product->create($data);

        if ($result['success']) {
            jsonResponse([
                'success' => true,
                'message' => $result['message'],
                'product_id' => $result['product_id']
            ], 201);
        } else {
            jsonResponse([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }
        break;

    // ============================================
    // UPDATE PRODUCT (Admin only)
    // ============================================
    case 'update':
        requireMethod('PUT');
        requireAdmin();

        $productId = (int)($id ?? $_GET['id'] ?? 0);
        if (!$productId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e produktit mungon'
            ], 400);
        }

        $data = getJsonInput();
        $result = $product->update($productId, $data);

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
    // DELETE PRODUCT (Admin only)
    // ============================================
    case 'delete':
        requireMethod('DELETE');
        requireAdmin();

        $productId = (int)($id ?? $_GET['id'] ?? 0);
        if (!$productId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e produktit mungon'
            ], 400);
        }

        $result = $product->delete($productId);

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

        $categories = $product->getCategories();

        jsonResponse([
            'success' => true,
            'data' => $categories
        ]);
        break;

    // ============================================
    // GET FEATURED PRODUCTS
    // ============================================
    case 'featured':
        requireMethod('GET');

        $limit = (int)($_GET['limit'] ?? 8);
        $featured = $product->getFeatured($limit);

        jsonResponse([
            'success' => true,
            'data' => $featured
        ]);
        break;

    // ============================================
    // SEARCH PRODUCTS
    // ============================================
    case 'search':
        requireMethod('GET');

        $query = $_GET['q'] ?? '';
        if (strlen($query) < 2) {
            jsonResponse([
                'success' => false,
                'message' => 'Kërkimi duhet të ketë të paktën 2 karaktere'
            ], 400);
        }

        $results = $product->search($query);

        jsonResponse([
            'success' => true,
            'data' => $results,
            'count' => count($results)
        ]);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
