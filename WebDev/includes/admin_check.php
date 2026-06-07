<?php
/**
 * ADMIN CHECK - Verifikimi i rolit admin
 * ======================================
 * Përfshije këtë skedar në faqet e panelit admin.
 * Nëse përdoruesi nuk është admin, ridrejtohet.
 *
 * SI PËRDORET:
 * require_once __DIR__ . '/../includes/admin_check.php';
 */

// Sigurohu që init.php është ngarkuar
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../includes/init.php';
}

// Kontrollo nëse përdoruesi është i loguar
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    setFlash('warning', 'Ju duhet të identifikoheni për të vazhduar.');
    redirect('auth/login.php');
}

// Kontrollo nëse përdoruesi është admin
if (!isAdmin()) {
    // Logo tentativën e aksesit të paautorizuar
    logUserAction(getCurrentUserId(), 'unauthorized_admin_access', 'Tentativë aksesi në panelin admin');

    setFlash('error', 'Nuk keni autorizim për të aksesuar këtë faqe.');
    redirect('');
}
