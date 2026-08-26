<?php
/**
 * KONSTANTET E APLIKACIONIT
 * =========================
 * Konstantet globale që përdoren në të gjithë aplikacionin.
 */

// Parandalon ngarkimin e dyfishtë
if (defined('SITE_NAME')) {
    return;
}

// ============================================
// INFORMACIONE TË SAJTIT
// ============================================
define('SITE_NAME', 'Web Platform');

// SITE_URL: nga APP_URL (Docker/.env), përndryshe zbulohet nga kërkesa aktuale.
// I hardcoduar te 'localhost:8080' e thyente redirect-in (p.sh. te verify_2fa.php)
// sapo aplikacioni aksesohej nga një host/portë tjetër nga default-i i Docker.
if ($envUrl = getenv('APP_URL')) {
    define('SITE_URL', rtrim($envUrl, '/'));
} elseif (!empty($_SERVER['HTTP_HOST'])) {
    // Prapa një proxy/tunnel (ngrok, Codespaces, nginx/Traefik, etj.) që bën TLS
    // termination, PHP e sheh kërkesën si HTTP edhe kur shfletuesi është në HTTPS —
    // atëherë X-Forwarded-Proto tregon protokollin e vërtetë. Pa këtë, SITE_URL dilte
    // 'http://' ndërsa faqja ishte 'https://', dhe fetch() bllokohej si "mixed content"
    // (butoni "spin"-te por asgjë nuk ndodhte, pa gabim të dukshëm).
    $forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
    if ($forwardedProto === 'https' || $forwardedProto === 'http') {
        $scheme = $forwardedProto;
    } elseif ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? null) == 443) {
        $scheme = 'https';
    } else {
        $scheme = 'http';
    }
    define('SITE_URL', $scheme . '://' . $_SERVER['HTTP_HOST']);
} else {
    define('SITE_URL', 'http://localhost:8080');
}

define('SITE_EMAIL', 'info@webplatform.com');

// ============================================
// PATHS - RRUGËT E DOSJEVE
// ============================================
define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// URL paths
define('CSS_URL', SITE_URL . '/public/css');
define('JS_URL', SITE_URL . '/public/js');
define('IMAGES_URL', SITE_URL . '/public/images');

// ============================================
// KONFIGURIME PËR SESIONE
// ============================================
define('SESSION_LIFETIME', 900);        // 15 minuta në sekonda
define('MAX_LOGIN_ATTEMPTS', 7);        // Numri max i tentativave
define('LOCKOUT_TIME', 1800);           // 30 minuta bllokimi

// ============================================
// KONFIGURIME PËR REMEMBER ME
// ============================================
define('REMEMBER_ME_EXPIRY', 2592000);  // 30 ditë në sekonda

// ============================================
// KONFIGURIME PËR EMAIL
// ============================================
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASS', 'your-password');
define('FROM_EMAIL', 'noreply@webplatform.com');
define('FROM_NAME', 'Web Platform');

// ============================================
// ÇELËSAT SEKRETË
// ============================================
define('SECRET_KEY', 'ndryshoni_kete_ne_nje_stringe_te_gjate_dhe_te_sigurt_1234567890');

// ============================================
// KONFIGURIME PËR SKEDARË
// ============================================
define('MAX_FILE_SIZE', 5242880);       // 5MB në bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PRODUCTS_PATH', UPLOADS_PATH . '/products/');
define('UPLOAD_USERS_PATH', UPLOADS_PATH . '/users/');

// ============================================
// KONFIGURIME PËR PAGINIM
// ============================================
define('ITEMS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// ============================================
// STATUSET E POROSIVE
// ============================================
define('ORDER_STATUS_PENDING', 'pending');
define('ORDER_STATUS_PROCESSING', 'processing');
define('ORDER_STATUS_SHIPPED', 'shipped');
define('ORDER_STATUS_DELIVERED', 'delivered');
define('ORDER_STATUS_CANCELLED', 'cancelled');

// ============================================
// ROLET E PËRDORUESVE
// ============================================
define('ROLE_USER', 'user');
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR', 'moderator');

// ============================================
// MODE ZHVILLIMI
// ============================================
define('DEBUG_MODE', true);

// Konfiguron shfaqjen e gabimeve bazuar në mode
if (DEBUG_MODE) {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/php_errors.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/php_errors.log');
}

// ============================================
// TIMEZONE
// ============================================
date_default_timezone_set('Europe/Tirane');

// ============================================
// VALUTA
// ============================================
define('DEFAULT_CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');
