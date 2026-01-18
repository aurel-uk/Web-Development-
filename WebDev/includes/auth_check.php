<?php
/**
 * AUTH CHECK - Verifikimi i autentikimit
 * ======================================
 * Përfshije këtë skedar në faqet që kërkojnë login.
 * Nëse përdoruesi nuk është i loguar, ridrejtohet në login.
 *
 * SI PËRDORET:
 * require_once __DIR__ . '/../includes/auth_check.php';
 */

// Sigurohu që init.php është ngarkuar
if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/../includes/init.php';
}

// Kontrollo nëse përdoruesi është i loguar
if (!isLoggedIn()) {
    // Ruaj URL-në aktuale për të ridrejtuar pas login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Vendos mesazh
    setFlash('warning', 'Ju duhet të identifikoheni për të vazhduar.');

    // Ridrejto në login
    redirect('auth/login.php');
}

// Kontrollo nëse llogaria është e verifikuar (nëse kërkohet)
if (defined('REQUIRE_VERIFIED_EMAIL') && REQUIRE_VERIFIED_EMAIL) {
    if (!isset($_SESSION['email_verified']) || !$_SESSION['email_verified']) {
        setFlash('warning', 'Ju duhet të verifikoni email-in tuaj për të vazhduar.');
        redirect('auth/verify_email.php');
    }
}
