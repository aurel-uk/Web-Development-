<?php
/**
 * FAQJA E PRODUKTEVE
 * ==================
 * Shfleton produktet me filtrim dhe kërkim.
 */

$pageTitle = 'Produktet - ' . SITE_NAME;
require_once __DIR__ . '/partials/header.php';

$productObj = new Product();

// Merr parametrat
$page = max(1, (int)($_GET['page'] ?? 1));
$categoryId = (int)($_GET['category'] ?? 0);
$search = sanitize($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$minPrice = (float)($_GET['min_price'] ?? 0);
$maxPrice = (float)($_GET['max_price'] ?? 0);

// Filtrat
$filters = [
    'category_id' => $categoryId ?: null,
    'search' => $search,
    'sort' => $sort,
    'min_price' => $minPrice ?: null,
    'max_price' => $maxPrice ?: null
];

// Merr produktet dhe kategoritë
$productsData = $productObj->getAllProducts($page, 12, $filters);
$products = $productsData['products'];
$totalPages = $productsData['pages'];
$categories = $productObj->getAllCategories();
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>">Kryefaqja</a></li>
            <li class="breadcrumb-item active">Produktet</li>
            <?php if ($categoryId && $category = $productObj->getCategory($categoryId)): ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($category['name']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 80px;">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filtrat</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kërko</label>
                            <input type="text"
                                   class="form-control"
                                   name="search"
                                   value="<?= htmlspecialchars($search) ?>"
                                   placeholder="Emri i produktit...">
                        </div>

                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kategoria</label>
                            <select name="category" class="form-select">
                                <option value="">Të gjitha</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?> (<?= $cat['product_count'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Çmimi</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number"
                                           class="form-control"
                                           name="min_price"
                                           placeholder="Min"
                                           value="<?= $minPrice ?: '' ?>"
                                           min="0">
                                </div>
                                <div class="col-6">
                                    <input type="number"
                                           class="form-control"
                                           name="max_price"
                                           placeholder="Max"
                                           value="<?= $maxPrice ?: '' ?>"
                                           min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Rendit sipas</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Më të rejat</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Çmimi (ulës)</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Çmimi (rritës)</option>
                                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Emri A-Z</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-2"></i>Apliko Filtrat
                            </button>
                            <a href="products.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x me-2"></i>Pastro Filtrat
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-0">
                        <?php if ($search): ?>
                            Rezultatet për "<?= htmlspecialchars($search) ?>"
                        <?php else: ?>
                            Produktet
                        <?php endif; ?>
                    </h4>
                    <p class="text-muted mb-0"><?= $productsData['total'] ?> produkte u gjetën</p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <!-- No Products -->
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <h4 class="mt-3">Asnjë produkt nuk u gjet</h4>
                    <p class="text-muted">Provo të ndryshosh filtrat ose të bësh një kërkim tjetër.</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="bi bi-arrow-left me-2"></i>Shiko të gjitha produktet
                    </a>
                </div>
            <?php else: ?>
                <!-- Products Grid -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($products as $product): ?>
                        <div class="col">
                            <div class="card product-card h-100">
                                <!-- Sale Badge -->
                                <?php if ($product['sale_price']): ?>
                                    <span class="badge badge-sale bg-danger">
                                        -<?= round((1 - $product['sale_price'] / $product['price']) * 100) ?>%
                                    </span>
                                <?php endif; ?>

                                <!-- Product Image -->
                                <a href="product-detail.php?slug=<?= $product['slug'] ?>">
                                    <img src="<?= SITE_URL ?>/assets/images/uploads/<?= $product['image'] ?? 'products/default.png' ?>"
                                         class="card-img-top"
                                         alt="<?= htmlspecialchars($product['name']) ?>">
                                </a>

                                <div class="card-body">
                                    <!-- Category -->
                                    <?php if ($product['category_name']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($product['category_name']) ?></small>
                                    <?php endif; ?>

                                    <!-- Title -->
                                    <h6 class="card-title mb-2">
                                        <a href="product-detail.php?slug=<?= $product['slug'] ?>" class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </a>
                                    </h6>

                                    <!-- Price -->
                                    <div class="mb-2">
                                        <?php if ($product['sale_price']): ?>
                                            <span class="price"><?= formatPrice($product['sale_price']) ?></span>
                                            <span class="old-price ms-2"><?= formatPrice($product['price']) ?></span>
                                        <?php else: ?>
                                            <span class="price"><?= formatPrice($product['price']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Stock -->
                                    <?php if ($product['stock'] > 0): ?>
                                        <small class="text-success">
                                            <i class="bi bi-check-circle me-1"></i>Në stok
                                        </small>
                                    <?php else: ?>
                                        <small class="text-danger">
                                            <i class="bi bi-x-circle me-1"></i>Jashtë stoku
                                        </small>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-white border-0 pt-0">
                                    <div class="d-grid">
                                        <?php if ($product['stock'] > 0): ?>
                                            <button class="btn btn-primary add-to-cart"
                                                    data-product-id="<?= $product['id'] ?>">
                                                <i class="bi bi-cart-plus me-2"></i>Shto në Shportë
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                <i class="bi bi-x-circle me-2"></i>Jashtë Stoku
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-5">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
window.SITE_URL = '<?= SITE_URL ?>';
window.CSRF_TOKEN = '<?= generateCSRFToken() ?>';
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
