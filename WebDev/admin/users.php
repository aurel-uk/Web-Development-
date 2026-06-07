<?php
/**
 * ADMIN - MENAXHIMI I PËRDORUESVE
 * ================================
 * Faqja për menaxhimin e përdoruesve.
 */

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Menaxhimi i Përdoruesve';
$db = Database::getInstance();

// Paginimi
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Kërkimi
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Query bazë
$where = '1=1';
$params = [];

if (!empty($search)) {
    $where .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $searchParam = "%{$search}%";
    $params = [$searchParam, $searchParam, $searchParam];
}

// Numri total
$totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE {$where}", $params)['count'];
$totalPages = ceil($totalUsers / $perPage);

// Merr përdoruesit
$users = $db->fetchAll(
    "SELECT u.*, r.name as role_name
     FROM users u
     LEFT JOIN roles r ON u.role_id = r.id
     WHERE {$where}
     ORDER BY u.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
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
                <h2><i class="bi bi-people me-2"></i>Përdoruesit</h2>
                <span class="badge bg-primary fs-6"><?= $totalUsers ?> total</span>
            </div>

            <!-- Search & Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" name="search"
                                       value="<?= htmlspecialchars($search) ?>"
                                       placeholder="Kërko sipas emrit, mbiemrit ose email-it...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Kërko
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="users.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i>Pastro
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Emri</th>
                                    <th>Email</th>
                                    <th>Roli</th>
                                    <th>Statusi</th>
                                    <th>Regjistruar</th>
                                    <th>Veprime</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Nuk u gjet asnjë përdorues.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                        <?php if (!empty($user['phone'])): ?>
                                                            <small class="d-block text-muted"><?= htmlspecialchars($user['phone']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $user['role_name'] === 'admin' ? 'bg-danger' : 'bg-secondary' ?>">
                                                    <?= ucfirst($user['role_name'] ?? 'user') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['email_verified']): ?>
                                                    <span class="badge bg-success">Verifikuar</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pa Verifikuar</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= formatDate($user['created_at'], 'd/m/Y') ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-outline-primary" title="Modifiko">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($user['id'] !== getCurrentUserId()): ?>
                                                        <button type="button" class="btn btn-outline-danger" title="Fshi"
                                                                data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                                data-user-id="<?= $user['id'] ?>"
                                                                data-user-name="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
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
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmo Fshirjen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Jeni të sigurt që doni të fshini përdoruesin <strong id="deleteUserName"></strong>?</p>
                <p class="text-danger small">Ky veprim nuk mund të zhbëhet.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                <form id="deleteForm" method="POST" action="<?= SITE_URL ?>/api/users.php" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <?= csrfField() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Fshi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('deleteModal').addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const userId = button.getAttribute('data-user-id');
    const userName = button.getAttribute('data-user-name');

    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
