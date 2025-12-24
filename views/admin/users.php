<?php
/**
 * MENAXHIMI I PËRDORUESVE (ADMIN)
 * ================================
 * Administratori mund të shohë, modifikojë, fshijë përdoruesit
 * dhe të ndryshojë rolet e tyre.
 */

$pageTitle = 'Menaxho Përdoruesit - ' . SITE_NAME;
require_once __DIR__ . '/../partials/header.php';

// Kontrollo aksesin admin
if (!isAdmin()) {
    setFlash('error', 'Nuk keni akses.');
    redirect('views/user/dashboard.php');
}

$db = Database::getInstance();
$userObj = new User();

// Parametrat e faqosjes
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$search = sanitize($_GET['search'] ?? '');

// Proceso veprimet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId > 0) {
        switch ($action) {
            case 'change_role':
                $roleId = (int)$_POST['role_id'];
                $result = $userObj->changeUserRole($userId, $roleId);
                setFlash($result['success'] ? 'success' : 'error', $result['message']);
                break;

            case 'toggle_status':
                $newStatus = (bool)$_POST['new_status'];
                $result = $userObj->toggleUserStatus($userId, $newStatus);
                setFlash($result['success'] ? 'success' : 'error', $result['message']);
                break;

            case 'delete':
                $result = $userObj->deleteUser($userId);
                setFlash($result['success'] ? 'success' : 'error', $result['message']);
                break;
        }
        redirect('views/admin/users.php?page=' . $page . ($search ? '&search=' . urlencode($search) : ''));
    }
}

// Merr përdoruesit
$usersData = $userObj->getAllUsers($page, $perPage, $search);
$users = $usersData['users'];
$totalPages = $usersData['pages'];

// Merr rolet
$roles = $db->fetchAll("SELECT * FROM roles");
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-none d-md-block sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                <a class="nav-link active" href="users.php"><i class="bi bi-people me-2"></i>Përdoruesit</a>
                <a class="nav-link" href="products.php"><i class="bi bi-box me-2"></i>Produktet</a>
                <a class="nav-link" href="categories.php"><i class="bi bi-tags me-2"></i>Kategoritë</a>
                <a class="nav-link" href="orders.php"><i class="bi bi-bag me-2"></i>Porositë</a>
                <a class="nav-link" href="payments.php"><i class="bi bi-credit-card me-2"></i>Pagesat</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 ms-auto">
            <div class="px-3">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="mb-0"><i class="bi bi-people me-2"></i>Menaxho Përdoruesit</h2>
                        <p class="text-muted">Gjithsej: <?= $usersData['total'] ?> përdorues</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus me-2"></i>Shto Përdorues
                    </button>
                </div>

                <!-- Search -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           name="search"
                                           value="<?= htmlspecialchars($search) ?>"
                                           placeholder="Kërko sipas emrit ose email...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Kërko</button>
                                <?php if ($search): ?>
                                    <a href="users.php" class="btn btn-outline-secondary">Pastro</a>
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
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Përdoruesi</th>
                                        <th>Email</th>
                                        <th>Roli</th>
                                        <th>Statusi</th>
                                        <th>Regjistruar</th>
                                        <th class="text-end">Veprime</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($users)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="bi bi-inbox display-6 text-muted"></i>
                                                <p class="text-muted mt-2">Asnjë përdorues nuk u gjet</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?= $user['id'] ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $user['profile_image'] ?? 'default.png' ?>"
                                                             class="rounded-circle me-2"
                                                             width="40" height="40"
                                                             style="object-fit: cover;">
                                                        <div>
                                                            <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                                            <?php if (!$user['is_verified']): ?>
                                                                <span class="badge bg-warning text-dark ms-1">Pa verifikuar</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <!-- Role Dropdown -->
                                                    <form method="POST" class="d-inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="change_role">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <select name="role_id"
                                                                class="form-select form-select-sm"
                                                                style="width: auto; display: inline-block;"
                                                                onchange="this.form.submit()"
                                                                <?= $user['id'] === getCurrentUserId() ? 'disabled' : '' ?>>
                                                            <?php foreach ($roles as $role): ?>
                                                                <option value="<?= $role['id'] ?>"
                                                                    <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>>
                                                                    <?= ucfirst($role['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </form>
                                                </td>
                                                <td>
                                                    <!-- Status Toggle -->
                                                    <form method="POST" class="d-inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <input type="hidden" name="new_status" value="<?= $user['is_active'] ? '0' : '1' ?>">
                                                        <button type="submit"
                                                                class="btn btn-sm <?= $user['is_active'] ? 'btn-success' : 'btn-danger' ?>"
                                                                <?= $user['id'] === getCurrentUserId() ? 'disabled' : '' ?>>
                                                            <?= $user['is_active'] ? 'Aktiv' : 'Joaktiv' ?>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="text-muted small">
                                                    <?= formatDate($user['created_at'], 'd/m/Y') ?>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group">
                                                        <a href="user-details.php?id=<?= $user['id'] ?>"
                                                           class="btn btn-sm btn-outline-primary"
                                                           title="Shiko">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <?php if ($user['id'] !== getCurrentUserId()): ?>
                                                            <form method="POST" class="d-inline"
                                                                  onsubmit="return confirm('Je i sigurt që dëshiron ta fshish këtë përdorues?')">
                                                                <?= csrfField() ?>
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                                <button type="submit"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        title="Fshi">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
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

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <nav>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Shto Përdorues të Ri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="add-user.php" method="POST">
                <?= csrfField() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Emri</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Mbiemri</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Fjalëkalimi</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Roli</label>
                            <select name="role_id" class="form-select">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= $role['id'] ?>"><?= ucfirst($role['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_verified" name="is_verified" checked>
                                <label class="form-check-label" for="is_verified">
                                    Email i verifikuar (skip email verification)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-primary">Shto Përdorues</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
