<?php
/**
 * POROSITË E MIA
 * ==============
 * Faqja ku përdoruesi shikon historikun e porosive.
 */

require_once __DIR__ . '/../includes/auth_check.php';

$pageTitle = 'Porositë e Mia';
$db = Database::getInstance();
$userId = getCurrentUserId();

// Merr porositë
$orders = $db->fetchAll(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC",
    [$userId]
);

// Statuset
$statuses = [
    'pending' => ['name' => 'Në Pritje', 'class' => 'bg-warning', 'icon' => 'bi-clock'],
    'processing' => ['name' => 'Duke u Procesuar', 'class' => 'bg-info', 'icon' => 'bi-gear'],
    'shipped' => ['name' => 'Dërguar', 'class' => 'bg-primary', 'icon' => 'bi-truck'],
    'delivered' => ['name' => 'Dorëzuar', 'class' => 'bg-success', 'icon' => 'bi-check-circle'],
    'cancelled' => ['name' => 'Anuluar', 'class' => 'bg-danger', 'icon' => 'bi-x-circle']
];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <h2 class="mb-4"><i class="bi bi-bag me-2"></i>Porositë e Mia</h2>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5">
            <i class="bi bi-bag-x display-1 text-muted"></i>
            <h4 class="mt-3">Nuk keni bërë asnjë porosi ende</h4>
            <p class="text-muted">Filloni të eksploroni produktet tona.</p>
            <a href="products.php" class="btn btn-primary">
                <i class="bi bi-box me-1"></i>Shiko Produktet
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($orders as $order): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Porosia #<?= htmlspecialchars($order['order_number']) ?></strong>
                                <span class="text-muted ms-3">
                                    <i class="bi bi-calendar me-1"></i><?= formatDate($order['created_at']) ?>
                                </span>
                            </div>
                            <span class="badge <?= $statuses[$order['status']]['class'] ?? 'bg-secondary' ?>">
                                <i class="bi <?= $statuses[$order['status']]['icon'] ?? 'bi-circle' ?> me-1"></i>
                                <?= $statuses[$order['status']]['name'] ?? $order['status'] ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <?php
                            // Merr produktet e porosisë
                            $orderItems = $db->fetchAll(
                                "SELECT oi.*, p.name, p.image
                                 FROM order_items oi
                                 JOIN products p ON oi.product_id = p.id
                                 WHERE oi.order_id = ?",
                                [$order['id']]
                            );
                            ?>

                            <div class="row g-3">
                                <?php foreach ($orderItems as $item): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="d-flex align-items-center p-2 bg-light rounded">
                                            <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($item['image'] ?? 'default.png') ?>"
                                                 alt="<?= htmlspecialchars($item['name']) ?>"
                                                 class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                            <div>
                                                <strong class="d-block"><?= htmlspecialchars($item['name']) ?></strong>
                                                <small class="text-muted">
                                                    <?= $item['quantity'] ?> x <?= formatPrice($item['price']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">Totali:</span>
                                <strong class="text-primary fs-5 ms-2"><?= formatPrice($order['total_amount']) ?></strong>
                            </div>
                            <div>
                                <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye me-1"></i>Shiko Detajet
                                </a>
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button type="button" class="btn btn-outline-danger btn-sm cancel-order"
                                            data-order-id="<?= $order['id'] ?>">
                                        <i class="bi bi-x me-1"></i>Anulo
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.cancel-order').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm('Jeni të sigurt që doni të anuloni këtë porosi?')) return;

            const orderId = this.dataset.orderId;

            try {
                if (typeof AppUtils !== 'undefined' && AppUtils.postJSON) {
                    const response = await AppUtils.postJSON('<?= SITE_URL ?>/api/orders.php', {
                        action: 'cancel',
                        order_id: orderId
                    });

                    if (response.success) {
                        AppUtils.showToast('success', 'Porosia u anulua me sukses.');
                        location.reload();
                    } else {
                        AppUtils.showToast('error', response.message);
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                if (typeof AppUtils !== 'undefined') {
                    AppUtils.showToast('error', 'Gabim në komunikim me serverin.');
                }
            }
        });
    });
});
</script>
