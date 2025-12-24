<?php
/**
 * DASHBOARD ADMIN
 * ================
 * Paneli kryesor për administratorët.
 * Shfaq statistika të përgjithshme dhe aksione të shpejta.
 */

$pageTitle = 'Admin Dashboard - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

// Kontrollo nëse është admin
if (!isAdmin()) {
    setFlash('error', 'Nuk keni akses në këtë faqe.');
    redirect('views/user/dashboard.php');
}

$db = Database::getInstance();

// Statistika
$totalUsers = $db->count('users');
$newUsersToday = $db->count('users', 'DATE(created_at) = CURDATE()');
$totalOrders = $db->count('orders');
$pendingOrders = $db->count('orders', "status = 'pending'");
$totalRevenue = $db->fetchOne(
    "SELECT SUM(total) as total FROM orders WHERE status = 'delivered'"
)['total'] ?? 0;
$totalProducts = $db->count('products');

// Përdoruesit e fundit
$recentUsers = $db->fetchAll(
    "SELECT u.*, r.name as role_name FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     ORDER BY u.created_at DESC LIMIT 5"
);

// Porositë e fundit
$recentOrders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     ORDER BY o.created_at DESC LIMIT 5"
);

// Aktiviteti i fundit
$recentActivity = $db->fetchAll(
    "SELECT l.*, u.first_name, u.last_name FROM user_logs l
     LEFT JOIN users u ON l.user_id = u.id
     ORDER BY l.created_at DESC LIMIT 10"
);
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar">
            <nav class="nav flex-column">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="bi bi-people me-2"></i>Përdoruesit
                </a>
                <a class="nav-link" href="products.php">
                    <i class="bi bi-box me-2"></i>Produktet
                </a>
                <a class="nav-link" href="categories.php">
                    <i class="bi bi-tags me-2"></i>Kategoritë
                </a>
                <a class="nav-link" href="orders.php">
                    <i class="bi bi-bag me-2"></i>Porositë
                </a>
                <a class="nav-link" href="payments.php">
                    <i class="bi bi-credit-card me-2"></i>Pagesat
                </a>
                <hr class="my-3 border-secondary">
                <a class="nav-link" href="messages.php">
                    <i class="bi bi-envelope me-2"></i>Mesazhet
                </a>
                <a class="nav-link" href="logs.php">
                    <i class="bi bi-journal-text me-2"></i>Loget
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear me-2"></i>Konfigurime
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-auto">
            <div class="px-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0">Dashboard</h2>
                        <p class="text-muted">Mirësevjen, <?= htmlspecialchars($_SESSION['user_name']) ?>!</p>
                    </div>
                    <div>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-calendar me-1"></i><?= date('d/m/Y H:i') ?>
                        </span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card h-100 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h3 class="stat-number mb-0"><?= $totalUsers ?></h3>
                                        <p class="text-muted mb-0 small">Përdorues Total</p>
                                    </div>
                                </div>
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i> +<?= $newUsersToday ?> sot
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card success h-100 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="bi bi-currency-euro"></i>
                                    </div>
                                    <div>
                                        <h3 class="stat-number mb-0"><?= formatPrice($totalRevenue) ?></h3>
                                        <p class="text-muted mb-0 small">Të Ardhura Totale</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card warning h-100 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="bi bi-bag"></i>
                                    </div>
                                    <div>
                                        <h3 class="stat-number mb-0"><?= $totalOrders ?></h3>
                                        <p class="text-muted mb-0 small">Porosi Total</p>
                                    </div>
                                </div>
                                <?php if ($pendingOrders > 0): ?>
                                    <small class="text-warning">
                                        <i class="bi bi-clock"></i> <?= $pendingOrders ?> në pritje
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xl-3">
                        <div class="card stat-card info h-100 border-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                        <i class="bi bi-box"></i>
                                    </div>
                                    <div>
                                        <h3 class="stat-number mb-0"><?= $totalProducts ?></h3>
                                        <p class="text-muted mb-0 small">Produkte</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Recent Users -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-people me-2"></i>Përdoruesit e Rinj</h5>
                                <a href="users.php" class="btn btn-sm btn-outline-primary">Shiko të Gjithë</a>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Emri</th>
                                                <th>Email</th>
                                                <th>Roli</th>
                                                <th>Data</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recentUsers as $user): ?>
                                                <tr>
                                                    <td>
                                                        <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                    </td>
                                                    <td class="text-muted small"><?= htmlspecialchars($user['email']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $user['role_name'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                                            <?= ucfirst($user['role_name']) ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-muted small"><?= formatDate($user['created_at'], 'd/m/Y') ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-bag me-2"></i>Porositë e Fundit</h5>
                                <a href="orders.php" class="btn btn-sm btn-outline-primary">Shiko të Gjitha</a>
                            </div>
                            <div class="card-body p-0">
                                <?php if (empty($recentOrders)): ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox text-muted display-6"></i>
                                        <p class="text-muted mt-2">Asnjë porosi ende</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nr.</th>
                                                    <th>Klienti</th>
                                                    <th>Total</th>
                                                    <th>Statusi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recentOrders as $order): ?>
                                                    <tr>
                                                        <td><strong><?= $order['order_number'] ?></strong></td>
                                                        <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                                        <td><?= formatPrice($order['total']) ?></td>
                                                        <td>
                                                            <?php
                                                            $badges = [
                                                                'pending' => 'bg-warning',
                                                                'processing' => 'bg-info',
                                                                'shipped' => 'bg-primary',
                                                                'delivered' => 'bg-success',
                                                                'cancelled' => 'bg-danger'
                                                            ];
                                                            ?>
                                                            <span class="badge <?= $badges[$order['status']] ?>"><?= ucfirst($order['status']) ?></span>
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

                    <!-- Activity Log -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Aktiviteti i Fundit</h5>
                                <a href="logs.php" class="btn btn-sm btn-outline-primary">Shiko të Gjitha</a>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recentActivity)): ?>
                                    <p class="text-muted text-center">Asnjë aktivitet i regjistruar</p>
                                <?php else: ?>
                                    <div class="timeline">
                                        <?php foreach ($recentActivity as $log): ?>
                                            <div class="d-flex mb-3">
                                                <div class="me-3">
                                                    <span class="badge rounded-pill bg-light text-dark">
                                                        <i class="bi bi-clock"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <strong><?= htmlspecialchars($log['first_name'] . ' ' . ($log['last_name'] ?? '')) ?></strong>
                                                    <span class="text-muted">-</span>
                                                    <span class="badge bg-secondary"><?= $log['action'] ?></span>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?= htmlspecialchars($log['description'] ?? '') ?>
                                                        <span class="ms-2"><?= formatDate($log['created_at']) ?></span>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
