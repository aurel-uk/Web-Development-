<?php
/**
 * API - SHPORTA
 * =============
 * Endpoint për operacionet e shportës.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

// Kontrollo autentikimin për veprime (jo për count)
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'count') {
    // Kthen numrin e produkteve në shportë
    if (!isLoggedIn()) {
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }

    $db = Database::getInstance();
    $count = $db->fetchOne(
        "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?",
        [getCurrentUserId()]
    );

    echo json_encode(['success' => true, 'count' => (int)($count['count'] ?? 0)]);
    exit;
}

// Kërko autentikim për veprime të tjera
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Duhet të jeni të loguar.']);
    exit;
}

$db = Database::getInstance();
$userId = getCurrentUserId();

// Merr të dhënat nga POST ose JSON
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = max(1, (int)($input['quantity'] ?? 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Produkt i pavlefshëm.']);
            exit;
        }

        // Kontrollo nëse produkti ekziston dhe ka stok
        $product = $db->fetchOne(
            "SELECT * FROM products WHERE id = ? AND is_active = 1",
            [$productId]
        );

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Produkti nuk u gjet.']);
            exit;
        }

        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Stoku i pamjaftueshëm.']);
            exit;
        }

        // Kontrollo nëse produkti është në shportë
        $existing = $db->fetchOne(
            "SELECT * FROM cart WHERE user_id = ? AND product_id = ?",
            [$userId, $productId]
        );

        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            if ($newQuantity > $product['stock']) {
                $newQuantity = $product['stock'];
            }
            $db->update('cart', ['quantity' => $newQuantity], 'id = ?', [$existing['id']]);
        } else {
            $db->insert('cart', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Produkti u shtua në shportë.']);
        break;

    case 'update':
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = max(1, (int)($input['quantity'] ?? 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Produkt i pavlefshëm.']);
            exit;
        }

        // Kontrollo stokun
        $product = $db->fetchOne("SELECT stock FROM products WHERE id = ?", [$productId]);
        if ($quantity > $product['stock']) {
            $quantity = $product['stock'];
        }

        $db->update('cart',
            ['quantity' => $quantity],
            'user_id = ? AND product_id = ?',
            [$userId, $productId]
        );

        // Llogarit totalin e ri
        $cartItems = $db->fetchAll(
            "SELECT c.quantity, p.price, p.sale_price
             FROM cart c
             JOIN products p ON c.product_id = p.id
             WHERE c.user_id = ?",
            [$userId]
        );

        $total = 0;
        foreach ($cartItems as $item) {
            $price = $item['sale_price'] ?: $item['price'];
            $total += $price * $item['quantity'];
        }

        echo json_encode(['success' => true, 'total' => $total]);
        break;

    case 'remove':
        $productId = (int)($input['product_id'] ?? 0);

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Produkt i pavlefshëm.']);
            exit;
        }

        $db->delete('cart', 'user_id = ? AND product_id = ?', [$userId, $productId]);

        echo json_encode(['success' => true, 'message' => 'Produkti u hoq nga shporta.']);
        break;

    case 'clear':
        $db->delete('cart', 'user_id = ?', [$userId]);
        echo json_encode(['success' => true, 'message' => 'Shporta u zbrazt.']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Veprim i pavlefshëm.']);
}
