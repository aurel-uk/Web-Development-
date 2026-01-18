<?php
/**
 * API - CHECKOUT
 * ==============
 * Endpoint për procesimin e porosive.
 */

require_once __DIR__ . '/../includes/init.php';

// Vetëm POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('error', 'Metodë e palejuar.');
    redirect('pages/cart.php');
}

// Kërko autentikim
if (!isLoggedIn()) {
    setFlash('warning', 'Duhet të jeni të loguar.');
    redirect('auth/login.php');
}

// Verifiko CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    setFlash('error', 'Kërkesë e pavlefshme.');
    redirect('pages/checkout.php');
}

$db = Database::getInstance();
$userId = getCurrentUserId();

// Merr produktet në shportë
$cartItems = $db->fetchAll(
    "SELECT c.*, p.name, p.price, p.sale_price, p.stock
     FROM cart c
     JOIN products p ON c.product_id = p.id
     WHERE c.user_id = ?",
    [$userId]
);

if (empty($cartItems)) {
    setFlash('warning', 'Shporta juaj është bosh.');
    redirect('pages/cart.php');
}

// Valido të dhënat
$firstName = sanitize($_POST['first_name'] ?? '');
$lastName = sanitize($_POST['last_name'] ?? '');
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$phone = sanitize($_POST['phone'] ?? '');
$address = sanitize($_POST['address'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$postalCode = sanitize($_POST['postal_code'] ?? '');
$notes = sanitize($_POST['notes'] ?? '');
$paymentMethod = sanitize($_POST['payment_method'] ?? 'cash');

if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($address) || empty($city)) {
    setFlash('error', 'Plotësoni të gjitha fushat e detyrueshme.');
    redirect('pages/checkout.php');
}

// Kontrollo stokun dhe llogarit totalin
$subtotal = 0;
foreach ($cartItems as $item) {
    if ($item['quantity'] > $item['stock']) {
        setFlash('error', "Produkti '{$item['name']}' nuk ka stok të mjaftueshëm.");
        redirect('pages/cart.php');
    }
    $price = $item['sale_price'] ?: $item['price'];
    $subtotal += $price * $item['quantity'];
}

$shipping = $subtotal > 50 ? 0 : 5;
$total = $subtotal + $shipping;

try {
    $db->beginTransaction();

    // Krijo porosinë
    $orderNumber = generateOrderNumber();

    $orderId = $db->insert('orders', [
        'user_id' => $userId,
        'order_number' => $orderNumber,
        'status' => ORDER_STATUS_PENDING,
        'subtotal' => $subtotal,
        'shipping_cost' => $shipping,
        'total_amount' => $total,
        'payment_method' => $paymentMethod,
        'shipping_first_name' => $firstName,
        'shipping_last_name' => $lastName,
        'shipping_email' => $email,
        'shipping_phone' => $phone,
        'shipping_address' => $address,
        'shipping_city' => $city,
        'shipping_postal_code' => $postalCode,
        'notes' => $notes
    ]);

    // Shto produktet në order_items dhe përditëso stokun
    foreach ($cartItems as $item) {
        $price = $item['sale_price'] ?: $item['price'];

        $db->insert('order_items', [
            'order_id' => $orderId,
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'price' => $price
        ]);

        // Zvogëlo stokun
        $db->query(
            "UPDATE products SET stock = stock - ? WHERE id = ?",
            [$item['quantity'], $item['product_id']]
        );
    }

    // Pastro shportën
    $db->delete('cart', 'user_id = ?', [$userId]);

    // Logo porosinë
    logUserAction($userId, 'order_created', "Porosi e re: {$orderNumber}");

    $db->commit();

    // TODO: Dërgo email konfirmimi
    // sendOrderConfirmation($email, $orderNumber);

    $_SESSION['last_order_number'] = $orderNumber;

    setFlash('success', 'Porosia juaj u krye me sukses!');
    redirect('pages/order_success.php');

} catch (Exception $e) {
    $db->rollback();

    if (DEBUG_MODE) {
        setFlash('error', 'Gabim: ' . $e->getMessage());
    } else {
        setFlash('error', 'Ndodhi një gabim. Provoni përsëri.');
    }

    redirect('pages/checkout.php');
}
