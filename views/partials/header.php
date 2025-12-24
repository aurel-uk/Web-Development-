<?php
/**
 * HEADER - Pjesa e sipërme e faqes
 * ================================
 * Kjo përfshihet në fillim të çdo faqeje.
 * Përmban: DOCTYPE, head (CSS, meta), navbar
 */

// Inicializo aplikacionin
require_once __DIR__ . '/../../includes/init.php';

// Merr titullin e faqes (vendoset para përfshirjes së header)
$pageTitle = $pageTitle ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $pageDescription ?? 'Platformë moderne web' ?>">

    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">

    <?php if (isset($extraCSS)): ?>
        <?= $extraCSS ?>
    <?php endif; ?>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand fw-bold" href="<?= SITE_URL ?>">
                <i class="bi bi-globe2 me-2"></i><?= SITE_NAME ?>
            </a>

            <!-- Hamburger Menu (Mobile) -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>">
                            <i class="bi bi-house me-1"></i>Kryefaqja
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/views/products.php">
                            <i class="bi bi-box me-1"></i>Produktet
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= SITE_URL ?>/views/contact.php">
                            <i class="bi bi-envelope me-1"></i>Kontakt
                        </a>
                    </li>
                </ul>

                <!-- User Menu -->
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <!-- Cart Icon -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/views/cart.php">
                                <i class="bi bi-cart3"></i>
                                <span class="badge bg-danger" id="cart-count">0</span>
                            </a>
                        </li>

                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['user_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if (isAdmin()): ?>
                                    <li>
                                        <a class="dropdown-item" href="<?= SITE_URL ?>/views/admin/dashboard.php">
                                            <i class="bi bi-speedometer2 me-2"></i>Paneli Admin
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="<?= SITE_URL ?>/views/user/dashboard.php">
                                        <i class="bi bi-grid me-2"></i>Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= SITE_URL ?>/views/user/profile.php">
                                        <i class="bi bi-person me-2"></i>Profili Im
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= SITE_URL ?>/views/user/orders.php">
                                        <i class="bi bi-bag me-2"></i>Porositë
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/views/auth/logout.php">
                                        <i class="bi bi-box-arrow-right me-2"></i>Dil
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= SITE_URL ?>/views/auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Hyr
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-light btn-sm ms-2" href="<?= SITE_URL ?>/views/auth/register.php">
                                <i class="bi bi-person-plus me-1"></i>Regjistrohu
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        <?= displayFlash() ?>
    </div>

    <!-- Main Content Start -->
    <main>
