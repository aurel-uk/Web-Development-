<?php
/**
 * SKEDARI I INICIALIZIMIT
 * ========================
 * Ky skedar ngarkohet në fillim të çdo faqeje.
 */

// Fillo sesionin para çdo gjëje tjetër
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0);
    session_start();
}

// Gjej rrugën e saktë për Config (Windows vs Linux case sensitivity)
$configPath = __DIR__ . '/../Config';
if (!is_dir($configPath)) {
    $configPath = __DIR__ . '/../config';
}

// Ngarko konstantet
require_once $configPath . '/constants.php';

// Ngarko databazën
require_once $configPath . '/database.php';

// Autoload klasat
spl_autoload_register(function ($className) {
    $paths = [
        __DIR__ . '/../Classes/' . $className . '.php',
        __DIR__ . '/../classes/' . $className . '.php',
    ];
    foreach ($paths as $classFile) {
        if (file_exists($classFile)) {
            require_once $classFile;
            return;
        }
    }
});

// Ngarko funksionet ndihmëse
require_once __DIR__ . '/functions.php';

// Kontrollo sesionin e skaduar
if (function_exists('checkSessionTimeout')) {
    checkSessionTimeout();
}

// Kontrollo "Remember Me"
if (function_exists('isLoggedIn') && function_exists('autoLogin')) {
    if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
        autoLogin();
    }
}
