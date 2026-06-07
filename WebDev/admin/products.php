<?php
/**
 * ADMIN - MENAXHIMI I PRODUKTEVE
 * ==============================
 * Faqja për menaxhimin e produkteve.
 */

require_once __DIR__ . '/../includes/admin_check.php';

$pageTitle = 'Menaxhimi i Produkteve';
$db = Database::getInstance();

// Paginimi
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ADMIN_ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filtrat
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Query bazë
$where = '1=1';
$params = [];

if (!empty($search)) {
    $where .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if ($category > 0) {
    $where .= " AND p.category_id = ?";
    $params[] = $category;
}

// Numri total
$totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products p WHERE {$where}", $params)['count'];
$totalPages = ceil($totalProducts / $perPage);

// Merr produktet
$products = $db->fetchAll(
    "SELECT p.*, c.name as category_name
     FROM products p
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE {$where}
     ORDER BY p.created_at DESC
     LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Merr kategoritë për filtër
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");

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
                <h2><i class="bi bi-box-seam me-2"></i>Produktet</h2>
                <a href="product_add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>Shto Produkt
                </a>
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
                                       placeholder="Kërko produkte...">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="category" class="form-select">
                                <option value="0">Të gjitha kategoritë</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Filtro
                            </button>
                            <?php if (!empty($search) || $category > 0): ?>
                                <a href="products.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x me-1"></i>Pastro
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Imazhi</th>
                                    <th>Emri</th>
                                    <th>Kategoria</th>
                                    <th>Çmimi</th>
                                    <th>Stoku</th>
                                    <th>Statusi</th>
                                    <th>Veprime</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($products)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Nuk u gjet asnjë produkt.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($product['image'] ?? 'default.png') ?>"
                                                     alt="<?= htmlspecialchars($product['name']) ?>"
                                                     class="rounded" style="width: 50px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                                <small class="d-block text-muted"><?= truncate($product['description'], 50) ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= htmlspecialchars($product['category_name'] ?? 'Pa kategori') ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-primary"><?= formatPrice($product['price']) ?></strong>
                                                <?php if (!empty($product['sale_price'])): ?>
                                                    <br><small class="text-success"><?= formatPrice($product['sale_price']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['stock'] <= 0): ?>
                                                    <span class="badge bg-danger">Stok 0</span>
                                                <?php elseif ($product['stock'] <= 5): ?>
                                                    <span class="badge bg-warning"><?= $product['stock'] ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?= $product['stock'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($product['is_active']): ?>
                                                    <span class="badge bg-success">Aktiv</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Joaktiv</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary" title="Modifiko">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger" title="Fshi"
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal"
                                                            data-product-id="<?= $product['id'] ?>"
                                                            data-product-name="<?= htmlspecialchars($product['name']) ?>">
                                                        <i class="bi bi-trash"></i>
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
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>">
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
                <p>Jeni të sigurt që doni të fshini produktin <strong id="deleteProductName"></strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                <form id="deleteForm" method="POST" action="<?= SITE_URL ?>/api/products.php" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" id="deleteProductId">
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
    const productId = button.getAttribute('data-product-id');
    const productName = button.getAttribute('data-product-name');

    document.getElementById('deleteProductId').value = productId;
    document.getElementById('deleteProductName').textContent = productName;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
