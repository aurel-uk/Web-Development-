<?php
/**
 * ADMIN SIDEBAR
 * =============
 * Sidebar për navigim në panelin admin.
 */

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<nav class="sidebar d-flex flex-column rounded-3 p-2">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>" href="index.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>" href="users.php">
                <i class="bi bi-people"></i> Përdoruesit
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>" href="products.php">
                <i class="bi bi-box-seam"></i> Produktet
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'categories' ? 'active' : '' ?>" href="categories.php">
                <i class="bi bi-tags"></i> Kategoritë
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'orders' ? 'active' : '' ?>" href="orders.php">
                <i class="bi bi-cart-check"></i> Porositë
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $currentPage === 'logs' ? 'active' : '' ?>" href="logs.php">
                <i class="bi bi-journal-text"></i> Logs
            </a>
        </li>

        <li class="nav-item mt-4">
            <hr class="border-secondary">
        </li>

        <li class="nav-item">
            <a class="nav-link" href="<?= SITE_URL ?>">
                <i class="bi bi-house"></i> Kryefaqja
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-danger" href="<?= SITE_URL ?>/auth/logout.php">
                <i class="bi bi-box-arrow-right"></i> Dil
            </a>
        </li>
    </ul>
</nav>
