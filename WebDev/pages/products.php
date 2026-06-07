<?php
/**
 * LISTA E PRODUKTEVE
 * ==================
 * Faqja që shfaq të gjitha produktet.
 */

$pageTitle = 'Produktet';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance();

// Paginimi
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filtrat
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Query bazë
$where = 'is_active = 1';
$params = [];

if ($category > 0) {
    $where .= " AND category_id = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where .= " AND (name LIKE ? OR description LIKE ?)";
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Sortimi
$orderBy = match($sort) {
    'price_asc' => 'price ASC',
    'price_desc' => 'price DESC',
    'name' => 'name ASC',
    default => 'created_at DESC'
};

// Numri total
$totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE {$where}", $params)['count'];
$totalPages = ceil($totalProducts / $perPage);

// Merr produktet
$products = $db->fetchAll(
    "SELECT * FROM products WHERE {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}",
    $params
);

// Merr kategoritë
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar Filters -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtro</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <!-- Kërkimi -->
                        <div class="mb-3">
                            <label class="form-label">Kërko</label>
                            <input type="text" name="search" class="form-control"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Emri i produktit...">
                        </div>

                        <!-- Kategoritë -->
                        <div class="mb-3">
                            <label class="form-label">Kategoria</label>
                            <select name="category" class="form-select">
                                <option value="0">Të gjitha</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $category === (int)$cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sortimi -->
                        <div class="mb-3">
                            <label class="form-label">Rendit sipas</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Më të rejat</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Çmimi: Ulët-Lart</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Çmimi: Lart-Ulët</option>
                                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Emri A-Z</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Apliko
                        </button>
                        <?php if (!empty($search) || $category > 0 || $sort !== 'newest'): ?>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                                Pastro Filtrat
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Produktet</h2>
                <span class="text-muted"><?= $totalProducts ?> produkte</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h4 class="mt-3">Nuk u gjet asnjë produkt</h4>
                    <p class="text-muted">Provo të ndryshosh filtrat ose kërkimin.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card product-card h-100">
                                <?php if (!empty($product['sale_price'])): ?>
                                    <span class="badge bg-danger badge-sale">Ulje!</span>
                                <?php endif; ?>

                                <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($product['image'] ?? 'default.png') ?>"
                                     class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">

                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?= truncate($product['description'], 80) ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center mt-auto">
                                        <div>
                                            <?php if (!empty($product['sale_price'])): ?>
                                                <span class="price"><?= formatPrice($product['sale_price']) ?></span>
                                                <span class="old-price ms-2"><?= formatPrice($product['price']) ?></span>
                                            <?php else: ?>
                                                <span class="price"><?= formatPrice($product['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer bg-transparent border-0 pt-0">
                                    <div class="d-grid gap-2">
                                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i>Shiko
                                        </a>
                                        <?php if ($product['stock'] > 0): ?>
                                            <button type="button" class="btn btn-primary add-to-cart"
                                                    data-product-id="<?= $product['id'] ?>">
                                                <i class="bi bi-cart-plus me-1"></i>Shto në Shportë
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-secondary" disabled>
                                                <i class="bi bi-x-circle me-1"></i>Jashtë Stokut
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginimi -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&category=<?= $category ?>&search=<?= urlencode($search) ?>&sort=<?= $sort ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            if (typeof AppUtils !== 'undefined' && AppUtils.addToCart) {
                AppUtils.addToCart(productId);
            } else {
                alert('Duhet të identifikoheni për të shtuar në shportë');
                window.location.href = '<?= SITE_URL ?>/auth/login.php';
            }
        });
    });
});
</script>
