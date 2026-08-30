<?php
/**
 * DASHBOARD I PËRDORUESIT
 * ========================
 * Faqja kryesore e llogarisë: përmbledhje e shpejtë e porosive,
 * shportës dhe lidhje drejt profilit.
 */

require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = 'Dashboard';
$db = Database::getInstance();
$userId = getCurrentUserId();

$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);

$orderStats = $db->fetchOne(
    "SELECT COUNT(*) as total, COALESCE(SUM(total_amount), 0) as spent
     FROM orders WHERE user_id = ?",
    [$userId]
);

$cartCount = $db->fetchOne(
    "SELECT COALESCE(SUM(quantity), 0) as count FROM cart WHERE user_id = ?",
    [$userId]
);

$recentOrders = $db->fetchAll(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 3",
    [$userId]
);

$statuses = [
    'pending' => ['name' => 'Në Pritje', 'class' => 'bg-warning', 'icon' => 'bi-clock'],
    'processing' => ['name' => 'Duke u Procesuar', 'class' => 'bg-info', 'icon' => 'bi-gear'],
    'shipped' => ['name' => 'Dërguar', 'class' => 'bg-primary', 'icon' => 'bi-truck'],
    'delivered' => ['name' => 'Dorëzuar', 'class' => 'bg-success', 'icon' => 'bi-check-circle'],
    'cancelled' => ['name' => 'Anuluar', 'class' => 'bg-danger', 'icon' => 'bi-x-circle']
];

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-5">

    <!-- Përshëndetje -->
    <div class="d-flex align-items-center mb-4">
        <img src="<?= IMAGES_URL ?>/users/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
             alt="Avatar" class="rounded-circle me-3" style="width: 64px; height: 64px; object-fit: cover;">
        <div>
            <h2 class="mb-1">Mirësevjen, <?= htmlspecialchars($user['first_name']) ?>!</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-envelope me-1"></i><?= htmlspecialchars($user['email']) ?>
            </p>
        </div>
    </div>

    <!-- Statistika të shpejta -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card h-100 text-center p-3">
                <i class="bi bi-bag-check text-primary fs-1"></i>
                <h3 class="mt-2 mb-0"><?= (int) ($orderStats['total'] ?? 0) ?></h3>
                <p class="text-muted mb-0">Porositë e Mia</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center p-3">
                <i class="bi bi-cart3 text-success fs-1"></i>
                <h3 class="mt-2 mb-0"><?= (int) ($cartCount['count'] ?? 0) ?></h3>
                <p class="text-muted mb-0">Në Shportë</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 text-center p-3">
                <i class="bi bi-currency-euro text-warning fs-1"></i>
                <h3 class="mt-2 mb-0"><?= formatPrice((float) ($orderStats['spent'] ?? 0)) ?></h3>
                <p class="text-muted mb-0">Shpenzuar Gjithsej</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Porositë e fundit -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history me-2"></i>Porositë e Fundit</span>
                    <a href="orders.php" class="small">Shiko të gjitha</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-bag-x display-4 text-muted"></i>
                            <p class="text-muted mt-2 mb-3">Nuk keni bërë asnjë porosi ende.</p>
                            <a href="products.php" class="btn btn-primary btn-sm">
                                <i class="bi bi-box me-1"></i>Shiko Produktet
                            </a>
                        </div>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($recentOrders as $order): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>#<?= htmlspecialchars($order['order_number']) ?></strong>
                                        <small class="text-muted d-block"><?= formatDate($order['created_at']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge <?= $statuses[$order['status']]['class'] ?? 'bg-secondary' ?> mb-1">
                                            <?= $statuses[$order['status']]['name'] ?? $order['status'] ?>
                                        </span>
                                        <strong class="d-block"><?= formatPrice($order['total_amount']) ?></strong>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Lidhje të shpejta -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-lightning me-2"></i>Lidhje të Shpejta</div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-person me-2"></i>Profili Im
                    </a>
                    <a href="orders.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-bag me-2"></i>Porositë e Mia
                    </a>
                    <a href="cart.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-cart3 me-2"></i>Shporta
                    </a>
                    <a href="products.php" class="list-group-item list-group-item-action">
                        <i class="bi bi-box me-2"></i>Shiko Produktet
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
