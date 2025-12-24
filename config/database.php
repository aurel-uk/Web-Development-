<?php
/**
 * KONFIGURIMI I DATABAZËS
 * ========================
 * Ky skedar përmban të dhënat për tu lidhur me MySQL.
 *
 * SHPJEGIM për fillestarët:
 * - DB_HOST: Ku ndodhet serveri i databazës (localhost = në kompjuterin tënd)
 * - DB_NAME: Emri i databazës që do krijojmë
 * - DB_USER: Përdoruesi i MySQL (default në XAMPP është 'root')
 * - DB_PASS: Fjalëkalimi (në XAMPP default është bosh '')
 */

// Konstante për lidhjen me databazën
define('DB_HOST', 'localhost');
define('DB_NAME', 'web_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Konstante të përgjithshme të aplikacionit
define('SITE_NAME', 'Web Platform');
define('SITE_URL', 'http://localhost/Web-Development-');

// Konfigurime për sesione
define('SESSION_LIFETIME', 900); // 15 minuta në sekonda (900 = 15 * 60)
define('MAX_LOGIN_ATTEMPTS', 7);
define('LOCKOUT_TIME', 1800); // 30 minuta në sekonda

// Konfigurime për email
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@example.com');
define('SMTP_PASS', 'your-password');
define('FROM_EMAIL', 'noreply@webplatform.com');
define('FROM_NAME', 'Web Platform');

// Çelësat sekretë për siguri (ndrysho këto në prodhim!)
define('SECRET_KEY', 'ndryshoni_kete_ne_nje_stringe_te_gjate_dhe_te_sigurt_1234567890');
define('REMEMBER_ME_EXPIRY', 2592000); // 30 ditë në sekonda

// Konfigurime për ngarkimin e skedarëve
define('MAX_FILE_SIZE', 5242880); // 5MB në bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_PATH', __DIR__ . '/../assets/images/uploads/');

// Mode zhvillimi (vendos false në prodhim)
define('DEBUG_MODE', true);

// Mos shfaq gabimet në prodhim
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
