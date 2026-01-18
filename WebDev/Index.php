<?php
/**
 * KRYEFAQJA - Homepage
 * ====================
 * Faqja kryesore e platformës.
 */

// Ngarko konfigurimin
require_once __DIR__ . '/includes/init.php';

$pageTitle = 'Mirësevini - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
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
                        <a href="<?= SITE_URL ?>/pages/dashboard.php" class="btn btn-outline-light btn-lg">
                            <i class="bi bi-grid me-2"></i>Dashboard
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

<!-- Features Section -->
<section class="py-5">
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
<section class="bg-light py-5">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
