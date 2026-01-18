<?php
/**
 * KRYEFAQJA - Homepage
 * ====================
 * Entry point kryesor i aplikacionit.
 * Faqja e parë që përdoruesit shohin.
 */

$pageTitle = 'Mirësevini';
require_once __DIR__ . '/../includes/header.php';

$db = Database::getInstance();

// Merr produktet e fundit (featured)
$featuredProducts = $db->fetchAll(
    "SELECT * FROM products WHERE is_active = 1 ORDER BY created_at DESC LIMIT 8"
);

// Merr kategoritë
$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name LIMIT 6");
?>

<!-- Hero Section -->
<section class="bg-gradient-primary text-white py-5">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Mirësevini në Platformën Tonë</h1>
                <p class="lead mb-4">
                    Zbulo produktet më të mira dhe shërbime cilësore.
                    Regjistrohuni sot dhe filloni të eksploroni!
                </p>
                <div class="d-flex gap-3">
                    <?php if (!isLoggedIn()): ?>
                        <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-light btn-lg">
                            <i class="bi bi-person-plus me-2"></i>Regjistrohu
                        </a>
                        <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-box me-2"></i>Shiko Produktet
                        </a>
                    <?php else: ?>
                        <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-light btn-lg">
                            <i class="bi bi-box me-2"></i>Shiko Produktet
                        </a>
                        <a href="<?= SITE_URL ?>/pages/profile.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-person me-2"></i>Profili Im
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6 text-center mt-5 mt-lg-0">
                <i class="bi bi-globe2 display-1" style="font-size: 15rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<?php if (!empty($categories)): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Kategoritë</h2>
            <p class="text-muted">Eksploro sipas kategorive</p>
        </div>

        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-4 col-lg-2">
                    <a href="<?= SITE_URL ?>/pages/products.php?category=<?= $category['id'] ?>"
                       class="card h-100 text-decoration-none text-center p-3">
                        <div class="card-body">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                                <i class="bi bi-tag text-primary fs-3"></i>
                            </div>
                            <h6 class="card-title mb-0"><?= htmlspecialchars($category['name']) ?></h6>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Featured Products Section -->
<?php if (!empty($featuredProducts)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-0">Produktet e Fundit</h2>
                <p class="text-muted mb-0">Zbulo produktet tona më të reja</p>
            </div>
            <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-outline-primary">
                Shiko të Gjitha <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card product-card h-100">
                        <?php if (!empty($product['sale_price'])): ?>
                            <span class="badge bg-danger badge-sale">Ulje!</span>
                        <?php endif; ?>

                        <img src="<?= IMAGES_URL ?>/products/<?= htmlspecialchars($product['image'] ?? 'default.png') ?>"
                             class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">

                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title"><?= htmlspecialchars($product['name']) ?></h6>
                            <p class="card-text text-muted small flex-grow-1">
                                <?= truncate($product['description'] ?? '', 60) ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <div>
                                    <?php if (!empty($product['sale_price'])): ?>
                                        <span class="price"><?= formatPrice($product['sale_price']) ?></span>
                                        <span class="old-price ms-1"><?= formatPrice($product['price']) ?></span>
                                    <?php else: ?>
                                        <span class="price"><?= formatPrice($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-transparent border-0 pt-0">
                            <a href="<?= SITE_URL ?>/pages/product_detail.php?id=<?= $product['id'] ?>"
                               class="btn btn-primary w-100">
                                <i class="bi bi-eye me-1"></i>Shiko
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Pse të na zgjidhni ne?</h2>
            <p class="text-muted">Disa arsye pse klientët na besojnë</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-shield-check text-primary fs-1"></i>
                        </div>
                        <h5 class="card-title">Siguri Maksimale</h5>
                        <p class="card-text text-muted">
                            Të dhënat tuaja janë të mbrojtura me teknologjinë më të avancuar të sigurisë.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-truck text-success fs-1"></i>
                        </div>
                        <h5 class="card-title">Dërgim i Shpejtë</h5>
                        <p class="card-text text-muted">
                            Dërgojmë në të gjithë Shqipërinë brenda 24-48 orëve.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 text-center p-4">
                    <div class="card-body">
                        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex p-3 mb-3">
                            <i class="bi bi-headset text-warning fs-1"></i>
                        </div>
                        <h5 class="card-title">Mbështetje 24/7</h5>
                        <p class="card-text text-muted">
                            Ekipi ynë është gjithmonë i gatshëm t'ju ndihmojë me çdo pyetje.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Gati të filloni?</h2>
        <p class="text-muted mb-4">Bashkohuni me mijëra klientë të kënaqur</p>
        <?php if (!isLoggedIn()): ?>
            <a href="<?= SITE_URL ?>/auth/register.php" class="btn btn-primary btn-lg">
                <i class="bi bi-rocket-takeoff me-2"></i>Fillo Tani
            </a>
        <?php else: ?>
            <a href="<?= SITE_URL ?>/pages/products.php" class="btn btn-primary btn-lg">
                <i class="bi bi-box me-2"></i>Eksploro Produktet
            </a>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
