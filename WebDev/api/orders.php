<?php
/**
 * API ENDPOINTS - POROSITË
 * ========================
 * Endpoint standalone (jo vetëm përmes routerit api/index.php) që përdoret
 * drejtpërdrejt nga paneli i adminit (admin/orders.php -> POST /api/orders.php)
 * dhe nga pages/orders.php (anulim porosie nga vetë klienti).
 *
 * Veprime (action nga $_GET/$_POST):
 * - GET  ?action=list          - Porositë e përdoruesit të loguar
 * - GET  ?action=single&id=    - Detajet e një porosie
 * - GET  ?action=all           - Të gjitha porositë (admin)
 * - GET  ?action=stats         - Statistika porosish (admin)
 * - POST action=update_status  - Ndrysho statusin e porosisë (admin)
 * - POST action=cancel         - Anulo porosinë (vetë klienti, vetëm nëse 'pending')
 */

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/api_helpers.php';

header('Content-Type: application/json; charset=utf-8');

$jsonBody = getJsonInput();
$action = $_GET['action'] ?? $_POST['action'] ?? $jsonBody['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$product = new Product();

switch ($action) {
    // ============================================
    // GET USER ORDERS
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');
        $userId = requireAuth();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = clampPerPage((int)($_GET['per_page'] ?? 10));
        $offset = ($page - 1) * $perPage;

        $orders = $db->fetchAll(
            "SELECT * FROM orders
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [$userId]
        );

        $total = $db->count('orders', 'user_id = ?', [$userId]);

        jsonResponse([
            'success' => true,
            'data' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ]);
        break;

    // ============================================
    // GET SINGLE ORDER
    // ============================================
    case 'single':
        requireMethod('GET');
        $userId = requireAuth();

        $orderId = (int)($_GET['id'] ?? 0);
        $order = $orderId ? $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$orderId, $userId]
        ) : null;

        if (!$order && $orderId && isAdmin()) {
            $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);
        }

        if (!$order) {
            jsonResponse([
                'success' => false,
                'message' => 'Porosia nuk u gjet'
            ], 404);
        }

        $order['items'] = $db->fetchAll(
            "SELECT oi.*, p.name, p.image
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$orderId]
        );

        jsonResponse([
            'success' => true,
            'data' => $order
        ]);
        break;

    // ============================================
    // CANCEL ORDER (vetë klienti)
    // ============================================
    case 'cancel':
        requireMethod('POST');
        $userId = requireAuth();
        requireCsrf();

        $data = getJsonInput();
        $orderId = (int)($_POST['order_id'] ?? $data['order_id'] ?? 0);

        if (!$orderId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e porosisë mungon'
            ], 400);
        }

        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$orderId, $userId]
        );

        if (!$order) {
            jsonResponse([
                'success' => false,
                'message' => 'Porosia nuk u gjet'
            ], 404);
        }

        if ($order['status'] !== 'pending') {
            jsonResponse([
                'success' => false,
                'message' => 'Vetëm porositë në pritje mund të anulohen'
            ], 400);
        }

        $result = $product->updateOrderStatus($orderId, 'cancelled');

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    // ============================================
    // GET ALL ORDERS (Admin)
    // ============================================
    case 'all':
        requireMethod('GET');
        requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = clampPerPage((int)($_GET['per_page'] ?? 10));
        $status = $_GET['status'] ?? '';
        $offset = ($page - 1) * $perPage;

        $where = '1';
        $params = [];

        if (!empty($status)) {
            $where = 'o.status = ?';
            $params[] = $status;
        }

        $orders = $db->fetchAll(
            "SELECT o.*, u.first_name, u.last_name, u.email
             FROM orders o
             JOIN users u ON o.user_id = u.id
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $total = $db->count('orders o', $where, $params);

        jsonResponse([
            'success' => true,
            'data' => $orders,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ]);
        break;

    // ============================================
    // UPDATE ORDER STATUS (Admin)
    // ============================================
    case 'update_status':
        requireMethod('POST');
        requireAdmin();
        requireCsrf();

        $data = getJsonInput();
        $orderId = (int)($_POST['order_id'] ?? $data['order_id'] ?? 0);
        $status = $_POST['status'] ?? $data['status'] ?? '';

        if (!$orderId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e porosisë mungon'
            ], 400);
        }

        $order = $db->fetchOne("SELECT id FROM orders WHERE id = ?", [$orderId]);

        if (!$order) {
            jsonResponse([
                'success' => false,
                'message' => 'Porosia nuk u gjet'
            ], 404);
        }

        $result = $product->updateOrderStatus($orderId, $status);

        jsonResponse([
            'success' => $result['success'],
            'message' => $result['message']
        ], $result['success'] ? 200 : 400);
        break;

    // ============================================
    // ORDER STATISTICS (Admin)
    // ============================================
    case 'stats':
        requireMethod('GET');
        requireAdmin();

        $stats = [
            'total_orders' => $db->count('orders'),
            'pending' => $db->count('orders', "status = 'pending'"),
            'processing' => $db->count('orders', "status = 'processing'"),
            'shipped' => $db->count('orders', "status = 'shipped'"),
            'delivered' => $db->count('orders', "status = 'delivered'"),
            'cancelled' => $db->count('orders', "status = 'cancelled'"),
            'total_revenue' => $db->fetchOne(
                "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'"
            )['total']
        ];

        jsonResponse([
            'success' => true,
            'data' => $stats
        ]);
        break;

    default:
        jsonResponse([
            'success' => false,
            'message' => 'Veprim i panjohur: ' . $action
        ], 404);
}
