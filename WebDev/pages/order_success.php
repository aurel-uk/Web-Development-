<?php
/**
 * POROSIA U KRYE ME SUKSES
 * ========================
 * Faqja që shfaqet pas krijimit të suksesshëm të porosisë.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Porosia u Krye';
require_once __DIR__ . '/../includes/header.php';

$orderNumber = $_SESSION['last_order_number'] ?? null;

// Nëse nuk ka order number, ridrejto në home
if (!$orderNumber) {
    redirect('');
}

// Fshin order number nga sesioni (shfaqet vetëm 1 herë)
unset($_SESSION['last_order_number']);

// Merr detajet e porosisë
$db = Database::getInstance();
$order = $db->fetchOne(
    "SELECT * FROM orders WHERE order_number = ? AND user_id = ?",
    [$orderNumber, getCurrentUserId()]
);

if (!$order) {
    redirect('');
}

// Merr produktet e porosisë
$orderItems = $db->fetchAll(
    "SELECT oi.*, p.name, p.image FROM order_items oi
     JOIN products p ON oi.product_id = p.id
     WHERE oi.order_id = ?",
    [$order['id']]
);
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-4 mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                </div>
                <h1 class="display-5 fw-bold text-success">Porosia u Krye!</h1>
                <p class="lead text-muted">
                    Faleminderit për porosinë tuaj. Do t'ju kontaktojmë së shpejti.
                </p>
            </div>

            <!-- Order Details -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-receipt me-2"></i>Detajet e Porosisë
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Numri i Porosisë:</strong>
                            <span class="text-primary"><?= htmlspecialchars($orderNumber) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <strong>Data:</strong>
                            <?= formatDate($order['created_at']) ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong>Metoda e Pagesës:</strong>
                            <?php
                            $methods = [
                                'cash' => 'Pagesë në Dorëzim',
                                'bank' => 'Transfer Bankar',
                                'card' => 'Kartë'
                            ];
                            echo $methods[$order['payment_method']] ?? $order['payment_method'];
                            ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Statusi:</strong>
                            <span class="badge bg-warning">Në Pritje</span>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Adresa e Dërgesës:</h6>
                    <p class="mb-0">
                        <?= htmlspecialchars($order['shipping_first_name'] . ' ' . $order['shipping_last_name']) ?><br>
                        <?= htmlspecialchars($order['shipping_address']) ?><br>
                        <?= htmlspecialchars($order['shipping_city']) ?>
                        <?php if ($order['shipping_postal_code']): ?>
                            , <?= htmlspecialchars($order['shipping_postal_code']) ?>
                        <?php endif; ?><br>
                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($order['shipping_phone']) ?><br>
                        <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($order['shipping_email']) ?>
                    </p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-box me-2"></i>Produktet e Porositura</h5>
                </div>
                <div class="card-body p-0">
                    <?php foreach ($orderItems as $item): ?>
                        <div class="d-flex align-items-center p-3 border-bottom">
                            <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($item['image'] ?? 'default.png') ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                 class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">Sasia: <?= $item['quantity'] ?></small>
                            </div>
                            <strong class="text-primary">
                                <?= formatPrice($item['price'] * $item['quantity']) ?>
                            </strong>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Nëntotali:</span>
                        <span><?= formatPrice($order['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Transporti:</span>
                        <span>
                            <?php if ($order['shipping_cost'] == 0): ?>
                                <span class="text-success">Falas</span>
                            <?php else: ?>
                                <?= formatPrice($order['shipping_cost']) ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong class="fs-5">Totali:</strong>
                        <strong class="fs-5 text-primary"><?= formatPrice($order['total_amount']) ?></strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-center gap-3">
                <a href="<?= SITE_URL ?>/pages/orders.php" class="btn btn-primary">
                    <i class="bi bi-list-ul me-2"></i>Shiko Porositë e Mia
                </a>
                <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Vazhdo Blerjen
                </a>
            </div>

            <!-- Bank Transfer Info -->
            <?php if ($order['payment_method'] === 'bank'): ?>
                <div class="alert alert-info mt-4">
                    <h6><i class="bi bi-bank me-2"></i>Informacion për Transfer Bankar</h6>
                    <p class="mb-0">
                        Ju lutem kryeni pagesën në llogarinë tonë bankare:<br>
                        <strong>Banka:</strong> Banka Kombëtare<br>
                        <strong>IBAN:</strong> AL47 2121 1009 0000 0002 3569 8741<br>
                        <strong>Përfituesi:</strong> <?= SITE_NAME ?><br>
                        <strong>Referenca:</strong> <?= $orderNumber ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
