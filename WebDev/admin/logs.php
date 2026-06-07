<?php
/**
 * ADMIN - SHIKIMI I LOGEVE
 * ========================
 * Faqja për shikimin e logeve të sistemit.
 */

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Logs të Sistemit';
$db = Database::getInstance();

// Paginimi
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Filtrat
$action = isset($_GET['action']) ? sanitize($_GET['action']) : '';
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Query bazë
$where = '1=1';
$params = [];

if (!empty($action)) {
    $where .= " AND l.action = ?";
    $params[] = $action;
}

if ($userId > 0) {
    $where .= " AND l.user_id = ?";
    $params[] = $userId;
}

// Numri total
$totalLogs = $db->fetchOne(
    "SELECT COUNT(*) as count FROM user_logs l WHERE {$where}",
    $params
)['count'];
$totalPages = ceil($totalLogs / $perPage);

// Merr logs
$logs = $db->fetchAll(
    "SELECT l.*, u.first_name, u.last_name, u.email
     FROM user_logs l
     LEFT JOIN users u ON l.user_id = u.id
     WHERE {$where}
     ORDER BY l.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Merr veprimet unike për filtër
$actions = $db->fetchAll("SELECT DISTINCT action FROM user_logs ORDER BY action");

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
                <h2><i class="bi bi-journal-text me-2"></i>Logs</h2>
                <span class="badge bg-primary fs-6"><?= number_format($totalLogs) ?> regjistrime</span>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <select name="action" class="form-select">
                                <option value="">Të gjitha veprimet</option>
                                <?php foreach ($actions as $act): ?>
                                    <option value="<?= htmlspecialchars($act['action']) ?>"
                                            <?= $action === $act['action'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($act['action']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" name="user_id" class="form-control"
                                   value="<?= $userId > 0 ? $userId : '' ?>"
                                   placeholder="ID e përdoruesit">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filtro
                            </button>
                            <?php if (!empty($action) || $userId > 0): ?>
                                <a href="logs.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i>Pastro
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Përdoruesi</th>
                                    <th>Veprimi</th>
                                    <th>Përshkrimi</th>
                                    <th>IP</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Nuk u gjet asnjë log.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($logs as $log): ?>
                                        <tr>
                                            <td class="text-nowrap">
                                                <small><?= formatDate($log['created_at']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($log['user_id']): ?>
                                                    <a href="users.php?search=<?= urlencode($log['email']) ?>">
                                                        <?= htmlspecialchars($log['first_name'] . ' ' . $log['last_name']) ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Anonim</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $actionClasses = [
                                                    'login' => 'bg-success',
                                                    'logout' => 'bg-secondary',
                                                    'register' => 'bg-primary',
                                                    'login_failed' => 'bg-danger',
                                                    'password_reset' => 'bg-warning',
                                                    'profile_update' => 'bg-info'
                                                ];
                                                $class = $actionClasses[$log['action']] ?? 'bg-secondary';
                                                ?>
                                                <span class="badge <?= $class ?>">
                                                    <?= htmlspecialchars($log['action']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?= htmlspecialchars(truncate($log['description'], 80)) ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted"><?= htmlspecialchars($log['ip_address']) ?></small>
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
                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&action=<?= urlencode($action) ?>&user_id=<?= $userId ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&action=<?= urlencode($action) ?>&user_id=<?= $userId ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&action=<?= urlencode($action) ?>&user_id=<?= $userId ?>">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
