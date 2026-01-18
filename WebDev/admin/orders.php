<?php
/**
 * ADMIN - MENAXHIMI I POROSIVE
 * ============================
 * Faqja për menaxhimin e porosive.
 */

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Menaxhimi i Porosive';
$db = Database::getInstance();

// Paginimi
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filtrat
$status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Query bazë
$where = '1=1';
$params = [];

if (!empty($status)) {
    $where .= " AND o.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $where .= " AND (o.order_number LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Numri total
$totalOrders = $db->fetchOne(
    "SELECT COUNT(*) as count FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE {$where}",
    $params
)['count'];
$totalPages = ceil($totalOrders / $perPage);

// Merr porositë
$orders = $db->fetchAll(
    "SELECT o.*, u.first_name, u.last_name, u.email
     FROM orders o
     LEFT JOIN users u ON o.user_id = u.id
     WHERE {$where}
     ORDER BY o.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Statuset
$statuses = [
    'pending' => ['name' => 'Në Pritje', 'class' => 'bg-warning'],
    'processing' => ['name' => 'Duke u Procesuar', 'class' => 'bg-info'],
    'shipped' => ['name' => 'Dërguar', 'class' => 'bg-primary'],
    'delivered' => ['name' => 'Dorëzuar', 'class' => 'bg-success'],
    'cancelled' => ['name' => 'Anuluar', 'class' => 'bg-danger']
];

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
                <h2><i class="bi bi-cart-check me-2"></i>Porositë</h2>
                <span class="badge bg-primary fs-6"><?= $totalOrders ?> total</span>
            </div>

            <!-- Search & Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search"
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="Kërko (numri porosisë, emri, email)...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-select">
                                <option value="">Të gjitha statuset</option>
                                <?php foreach ($statuses as $key => $val): ?>
                                    <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>>
                                        <?= $val['name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filtro
                            </button>
                            <?php if (!empty($search) || !empty($status)): ?>
                                <a href="orders.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i>Pastro
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Nr. Porosie</th>
                                    <th>Klienti</th>
                                    <th>Total</th>
                                    <th>Statusi</th>
                                    <th>Data</th>
                                    <th>Veprime</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            Nuk u gjet asnjë porosi.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($order['order_number']) ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></strong>
                                                    <small class="d-block text-muted"><?= htmlspecialchars($order['email']) ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= formatPrice($order['total_amount']) ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge <?= $statuses[$order['status']]['class'] ?? 'bg-secondary' ?>">
                                                    <?= $statuses[$order['status']]['name'] ?? $order['status'] ?>
                                                </span>
                                            </td>
                                            <td><?= formatDate($order['created_at']) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary" title="Shiko Detajet">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-secondary" title="Ndrysho Statusin"
                                                            data-bs-toggle="modal" data-bs-target="#statusModal"
                                                            data-order-id="<?= $order['id'] ?>"
                                                            data-order-number="<?= htmlspecialchars($order['order_number']) ?>"
                                                            data-current-status="<?= $order['status'] ?>">
                                                        <i class="bi bi-arrow-repeat"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ndrysho Statusin</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= SITE_URL ?>/api/orders.php">
                <div class="modal-body">
                    <p>Porosia: <strong id="statusOrderNumber"></strong></p>
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="order_id" id="statusOrderId">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Statusi i Ri</label>
                        <select name="status" id="newStatus" class="form-select" required>
                            <?php foreach ($statuses as $key => $val): ?>
                                <option value="<?= $key ?>"><?= $val['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i>Ruaj
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('statusModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const orderId = button.getAttribute('data-order-id');
    const orderNumber = button.getAttribute('data-order-number');
    const currentStatus = button.getAttribute('data-current-status');

    document.getElementById('statusOrderId').value = orderId;
    document.getElementById('statusOrderNumber').textContent = orderNumber;
    document.getElementById('newStatus').value = currentStatus;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
