<?php
/**
 * DALJA NGA LLOGARIA
 * ==================
 * Përfundon sesionin dhe fshin cookies.
 */

require_once __DIR__ . '/../includes/init.php';

// Logo daljen
if (isLoggedIn()) {
    logUserAction(getCurrentUserId(), 'logout', 'Dalje nga llogaria');
}

// Fshi remember me token nëse ekziston
if (isset($_COOKIE['remember_token'])) {
    $db = Database::getInstance();
    $token = hash('sha256', $_COOKIE['remember_token']);
    $db->delete('remember_tokens', 'token = ?', [$token]);

    // Fshi cookie
    setcookie('remember_token', '', time() - 3600, '/');
}

// Fshi të gjitha të dhënat e sesionit
$_SESSION = [];

// Fshi cookie e sesionit
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Shkatërro sesionin
session_destroy();

// Vendos mesazh (në sesion të ri)
session_start();
setFlash('success', 'Ju dolët me sukses nga llogaria.');

// Ridrejto në kryefaqe
redirect('');
