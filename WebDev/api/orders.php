<?php
/**
 * API ENDPOINTS - POROSITË
 * ========================
 * Menaxhon porositë e klientëve.
 *
 * Endpoints:
 * - GET /api/orders - Lista e porosive të përdoruesit
 * - GET /api/orders/{id} - Detajet e porosisë
 * - POST /api/orders - Krijo porosi të re
 * - GET /api/orders/all - Të gjitha porositë (admin)
 * - PUT /api/orders/{id}/status - Ndrysho statusin (admin)
 */

$db = Database::getInstance();
$method = $_SERVER['REQUEST_METHOD'];

// Nëse action është numër, atëherë është ID
if (is_numeric($action)) {
    $id = (int)$action;
    $action = 'single';
}

switch ($action) {
    // ============================================
    // GET USER ORDERS
    // ============================================
    case '':
    case 'list':
        requireMethod('GET');
        $userId = requireAuth();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);
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
                'total_pages' => ceil($total / $perPage),
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

        $order = $db->fetchOne(
            "SELECT * FROM orders WHERE id = ? AND user_id = ?",
            [$id, $userId]
        );

        // Nëse është admin, lejo akses te çdo porosi
        if (!$order && isAdmin()) {
            $order = $db->fetchOne("SELECT * FROM orders WHERE id = ?", [$id]);
        }

        if (!$order) {
            jsonResponse([
                'success' => false,
                'message' => 'Porosia nuk u gjet'
            ], 404);
        }

        // Merr artikujt e porosisë
        $items = $db->fetchAll(
            "SELECT oi.*, p.name, p.image
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?",
            [$id]
        );

        $order['items'] = $items;

        jsonResponse([
            'success' => true,
            'data' => $order
        ]);
        break;

    // ============================================
    // CREATE ORDER
    // ============================================
    case 'create':
        requireMethod('POST');
        $userId = requireAuth();

        $data = getJsonInput();

        // Merr artikujt nga shporta
        $cartItems = $db->fetchAll(
            "SELECT c.*, p.name, p.price, p.stock
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = ?",
            [$userId]
        );

        if (empty($cartItems)) {
            jsonResponse([
                'success' => false,
                'message' => 'Shporta është bosh'
            ], 400);
        }

        // Valido të dhënat e dërgesës
        $shippingAddress = sanitize($data['shipping_address'] ?? '');
        $shippingCity = sanitize($data['shipping_city'] ?? '');
        $phone = sanitize($data['phone'] ?? '');

        if (empty($shippingAddress) || empty($shippingCity) || empty($phone)) {
            jsonResponse([
                'success' => false,
                'message' => 'Adresa, qyteti dhe telefoni janë të detyrueshëm'
            ], 400);
        }

        // Kontrollo stock dhe llogarit totalin
        $total = 0;
        foreach ($cartItems as $item) {
            if ($item['quantity'] > $item['stock']) {
                jsonResponse([
                    'success' => false,
                    'message' => "Stock i pamjaftueshëm për: {$item['name']}"
                ], 400);
            }
            $total += $item['price'] * $item['quantity'];
        }

        try {
            $db->beginTransaction();

            // Krijo porosinë
            $orderId = $db->insert('orders', [
                'user_id' => $userId,
                'total' => $total,
                'status' => 'pending',
                'shipping_address' => $shippingAddress,
                'shipping_city' => $shippingCity,
                'phone' => $phone,
                'notes' => sanitize($data['notes'] ?? '')
            ]);

            // Shto artikujt e porosisë
            foreach ($cartItems as $item) {
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Ul stock-un
                $db->query(
                    "UPDATE products SET stock = stock - ? WHERE id = ?",
                    [$item['quantity'], $item['product_id']]
                );
            }

            // Zbraz shportën
            $db->delete('cart', 'user_id = ?', [$userId]);

            $db->commit();

            logUserAction($userId, 'order_created', "Porosia #{$orderId} u krijua");

            jsonResponse([
                'success' => true,
                'message' => 'Porosia u krijua me sukses',
                'order_id' => $orderId,
                'total' => $total
            ], 201);

        } catch (Exception $e) {
            $db->rollback();
            jsonResponse([
                'success' => false,
                'message' => 'Ndodhi një gabim gjatë krijimit të porosisë'
            ], 500);
        }
        break;

    // ============================================
    // GET ALL ORDERS (Admin)
    // ============================================
    case 'all':
        requireMethod('GET');
        requireAdmin();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 10);
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
                'total_pages' => ceil($total / $perPage),
                'total_items' => $total,
                'per_page' => $perPage
            ]
        ]);
        break;

    // ============================================
    // UPDATE ORDER STATUS (Admin)
    // ============================================
    case 'status':
        requireMethod('PUT');
        requireAdmin();

        $data = getJsonInput();
        $orderId = (int)($id ?? $data['order_id'] ?? 0);
        $status = $data['status'] ?? '';

        $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if (!$orderId) {
            jsonResponse([
                'success' => false,
                'message' => 'ID e porosisë mungon'
            ], 400);
        }

        if (!in_array($status, $validStatuses)) {
            jsonResponse([
                'success' => false,
                'message' => 'Status i pavlefshëm. Statuset e lejuara: ' . implode(', ', $validStatuses)
            ], 400);
        }

        $order = $db->fetchOne("SELECT id FROM orders WHERE id = ?", [$orderId]);

        if (!$order) {
            jsonResponse([
                'success' => false,
                'message' => 'Porosia nuk u gjet'
            ], 404);
        }

        $db->update('orders', ['status' => $status], 'id = ?', [$orderId]);

        logUserAction(getCurrentUserId(), 'order_status_update', "Statusi i porosisë #{$orderId} u ndryshua në {$status}");

        jsonResponse([
            'success' => true,
            'message' => 'Statusi u përditësua me sukses'
        ]);
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
                "SELECT COALESCE(SUM(total), 0) as total FROM orders WHERE status != 'cancelled'"
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
