<?php
/**
 * DASHBOARD ROUTE COMPATIBILITY PAGE
 * ==================================
 * Keeps old dashboard links working by redirecting
 * to the real role-based dashboard page.
 */

require_once __DIR__ . '/../includes/init.php';

if (!isLoggedIn()) {
    redirect('auth/login.php');
}

$target = isAdmin() ? '../views/admin/dashboard.php' : '../views/user/dashboard.php';
redirect($target);
