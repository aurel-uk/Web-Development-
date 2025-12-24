<?php
/**
 * SKEDARI I INICIALIZIMIT
 * ========================
 * Ky skedar ngarkohet në fillim të çdo faqeje.
 * Ai përfshin:
 * - Konfigurimet
 * - Autoload të klasave
 * - Fillimin e sesionit
 * - Funksione ndihmëse globale
 *
 * SI PËRDORET:
 * Në fillim të çdo faqeje PHP, shkruaj:
 * require_once 'includes/init.php';
 */

// Fillo sesionin para çdo gjëje tjetër
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurime të sigurta për sesionin
    ini_set('session.cookie_httponly', 1);  // Ndalon aksesin JS te cookie
    ini_set('session.use_only_cookies', 1);  // Vetëm cookies, jo URL
    ini_set('session.cookie_secure', 0);     // 1 nëse ke HTTPS

    session_start();
}

// Ngarko konfigurimet
require_once __DIR__ . '/../config/database.php';

// Autoload: Ngarkon klasat automatikisht kur përdoren
spl_autoload_register(function ($className) {
    $classFile = __DIR__ . '/../classes/' . $className . '.php';
    if (file_exists($classFile)) {
        require_once $classFile;
    }
});

// Ngarko funksionet ndihmëse
require_once __DIR__ . '/functions.php';

// Kontrollo sesionin e skaduar
checkSessionTimeout();

// Kontrollo "Remember Me" nëse nuk ka sesion aktiv
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    autoLogin();
}
