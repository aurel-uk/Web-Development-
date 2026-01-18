<?php
/**
 * ADMIN DASHBOARD
 * ===============
 * Faqja kryesore e panelit administrativ.
 */

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Admin Dashboard';
$db = Database::getInstance();

// Merr statistikat
$stats = [
    'users' => $db->count('users'),
    'products' => $db->count('products'),
    'orders' => $db->count('orders'),
    'revenue' => $db->fetchOne("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status != 'cancelled'")['total'] ?? 0
];

// Merr porositë e fundit
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email
     FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC
     LIMIT 5"
);

// Merr përdoruesit e rinj
$recentUsers = $db->fetchAll(
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5"
);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2">
            <?php include __DIR__ . '/partials/sidebar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
                <span class="text-muted"><?= date('d/m/Y H:i') ?></span>
            </div>

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                <i class="bi bi-people"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Përdorues</h6>
                                <h3 class="stat-number mb-0"><?= number_format($stats['users']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card success h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Produkte</h6>
                                <h3 class="stat-number mb-0"><?= number_format($stats['products']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card warning h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Porosi</h6>
                                <h3 class="stat-number mb-0"><?= number_format($stats['orders']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card stat-card info h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                <i class="bi bi-currency-euro"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">Të Ardhura</h6>
                                <h3 class="stat-number mb-0"><?= formatPrice($stats['revenue']) ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Recent Orders -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-cart me-2"></i>Porositë e Fundit</h5>
                            <a href="orders.php" class="btn btn-sm btn-outline-primary">Shiko të Gjitha</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Klienti</th>
                                            <th>Total</th>
                                            <th>Statusi</th>
                                            <th>Data</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentOrders)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Nuk ka porosi ende.
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentOrders as $order): ?>
                                                <tr>
                                                    <td>#<?= $order['id'] ?></td>
                                                    <td>
                                                        <?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?>
                                                    </td>
                                                    <td><?= formatPrice($order['total_amount']) ?></td>
                                                    <td>
                                                        <?php
                                                        $statusClasses = [
                                                            'pending' => 'bg-warning',
                                                            'processing' => 'bg-info',
                                                            'shipped' => 'bg-primary',
                                                            'delivered' => 'bg-success',
                                                            'cancelled' => 'bg-danger'
                                                        ];
                                                        $statusNames = [
                                                            'pending' => 'Në Pritje',
                                                            'processing' => 'Duke u Procesuar',
                                                            'shipped' => 'Dërguar',
                                                            'delivered' => 'Dorëzuar',
                                                            'cancelled' => 'Anuluar'
                                                        ];
                                                        ?>
                                                        <span class="badge <?= $statusClasses[$order['status']] ?? 'bg-secondary' ?>">
                                                            <?= $statusNames[$order['status']] ?? $order['status'] ?>
                                                        </span>
                                                    </td>
                                                    <td><?= formatDate($order['created_at']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Përdorues të Rinj</h5>
                            <a href="users.php" class="btn btn-sm btn-outline-primary">Shiko</a>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($recentUsers)): ?>
                                    <li class="list-group-item text-center text-muted py-4">
                                        Nuk ka përdorues ende.
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <li class="list-group-item d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                                                <i class="bi bi-person text-primary"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                <small class="d-block text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
