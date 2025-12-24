<?php
/**
 * DASHBOARD I PËRDORUESIT
 * =======================
 * Faqja kryesore pas login-it për përdoruesit normalë.
 * Shfaq statistika dhe veprimet e shpejta.
 */

$pageTitle = 'Dashboard - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

// Kontrollo nëse përdoruesi është loguar
if (!isLoggedIn()) {
    setFlash('warning', 'Duhet të jesh i loguar për të aksesuar këtë faqe.');
    redirect('views/auth/login.php');
}

// Merr të dhënat e përdoruesit
$db = Database::getInstance();
$userId = getCurrentUserId();

// Statistika
$orderCount = $db->count('orders', 'user_id = ?', [$userId]);
$totalSpent = $db->fetchOne(
    "SELECT SUM(total) as total FROM orders WHERE user_id = ? AND status != 'cancelled'",
    [$userId]
)['total'] ?? 0;

// Porositë e fundit
$recentOrders = $db->fetchAll(
    "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
    [$userId]
);
?>

<div class="container py-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="bg-gradient-primary text-white rounded-3 p-4 shadow">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-2">
                            <i class="bi bi-sun me-2"></i>Mirësevjen, <?= htmlspecialchars($_SESSION['user_name']) ?>!
                        </h2>
                        <p class="mb-0 opacity-75">
                            Kjo është faqja juaj personale ku mund të menaxhosh llogarinë dhe porositë.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="profile.php" class="btn btn-light">
                            <i class="bi bi-person me-2"></i>Shiko Profilin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card stat-card success h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-bag-check"></i>
                    </div>
                    <div>
                        <h3 class="stat-number mb-0"><?= $orderCount ?></h3>
                        <p class="text-muted mb-0">Porosi Totale</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card info h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-currency-euro"></i>
                    </div>
                    <div>
                        <h3 class="stat-number mb-0"><?= formatPrice($totalSpent) ?></h3>
                        <p class="text-muted mb-0">Shpenzuar Total</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card stat-card warning h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <div>
                        <h3 class="stat-number mb-0">
                            <?= $orderCount >= 10 ? 'VIP' : ($orderCount >= 5 ? 'Gold' : 'Standard') ?>
                        </h3>
                        <p class="text-muted mb-0">Statusi Juaj</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Porositë e Fundit</h5>
                    <a href="orders.php" class="btn btn-sm btn-outline-primary">Shiko të Gjitha</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bag-x display-4 text-muted"></i>
                            <p class="text-muted mt-3">Nuk keni asnjë porosi ende.</p>
                            <a href="<?= SITE_URL ?>/views/products.php" class="btn btn-primary">
                                <i class="bi bi-bag-plus me-2"></i>Filloni Blerjen
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nr. Porosie</th>
                                        <th>Data</th>
                                        <th>Statusi</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td><strong><?= $order['order_number'] ?></strong></td>
                                            <td><?= formatDate($order['created_at']) ?></td>
                                            <td>
                                                <?php
                                                $statusBadges = [
                                                    'pending' => 'bg-warning',
                                                    'processing' => 'bg-info',
                                                    'shipped' => 'bg-primary',
                                                    'delivered' => 'bg-success',
                                                    'cancelled' => 'bg-danger'
                                                ];
                                                $statusNames = [
                                                    'pending' => 'Në Pritje',
                                                    'processing' => 'Në Procesim',
                                                    'shipped' => 'Dërguar',
                                                    'delivered' => 'Dorëzuar',
                                                    'cancelled' => 'Anuluar'
                                                ];
                                                ?>
                                                <span class="badge <?= $statusBadges[$order['status']] ?>">
                                                    <?= $statusNames[$order['status']] ?>
                                                </span>
                                            </td>
                                            <td><?= formatPrice($order['total']) ?></td>
                                            <td>
                                                <a href="order-details.php?id=<?= $order['id'] ?>"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-lightning me-2"></i>Veprime të Shpejta</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="profile.php" class="btn btn-outline-primary text-start">
                            <i class="bi bi-person me-2"></i>Ndrysho Profilin
                        </a>
                        <a href="profile.php#change-password" class="btn btn-outline-primary text-start">
                            <i class="bi bi-lock me-2"></i>Ndrysho Fjalëkalimin
                        </a>
                        <a href="orders.php" class="btn btn-outline-primary text-start">
                            <i class="bi bi-bag me-2"></i>Shiko Porositë
                        </a>
                        <a href="<?= SITE_URL ?>/views/cart.php" class="btn btn-outline-primary text-start">
                            <i class="bi bi-cart me-2"></i>Shiko Shportën
                        </a>
                        <a href="<?= SITE_URL ?>/views/products.php" class="btn btn-primary text-start">
                            <i class="bi bi-box me-2"></i>Shfleto Produktet
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card mt-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Informacione</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bi bi-envelope text-primary me-2"></i>
                            <span class="text-muted"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-shield-check text-success me-2"></i>
                            <span class="text-muted">Llogaria e Verifikuar</span>
                        </li>
                        <li>
                            <i class="bi bi-person-badge text-info me-2"></i>
                            <span class="text-muted">Roli: <?= ucfirst($_SESSION['user_role']) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
