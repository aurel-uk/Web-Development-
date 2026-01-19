<?php
/**
 * API - VERIFIKIMI 2FA
 * ====================
 * Endpoint për verifikimin e kodit Two-Factor Authentication.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/init.php';

// Vetëm POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metodë e palejuar.']);
    exit;
}

// Verifiko CSRF
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Token i pavlefshëm.']);
    exit;
}

// Kontrollo nëse ka sesion 2FA aktiv
if (!isset($_SESSION['2fa_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesioni ka skaduar. Ju lutem hyni përsëri.']);
    exit;
}

$db = Database::getInstance();
$userId = $_SESSION['2fa_user_id'];
$remember = $_SESSION['2fa_remember'] ?? false;

// Kontrollo veprimin (verifikim ose ridërgim)
$action = $_POST['action'] ?? 'verify';

if ($action === 'resend') {
    // Ridërgo kodin
    resend2FACode($db, $userId);
} else {
    // Verifiko kodin
    verify2FACode($db, $userId, $remember);
}

/**
 * Verifikon kodin 2FA
 */
function verify2FACode($db, $userId, $remember) {
    $code = preg_replace('/[^0-9]/', '', $_POST['code'] ?? '');

    if (strlen($code) !== 6) {
        echo json_encode(['success' => false, 'message' => 'Kodi duhet të jetë 6 shifra.']);
        exit;
    }

    // Kontrollo kodin në databazë
    $storedCode = $db->fetchOne(
        "SELECT * FROM two_factor_codes
         WHERE user_id = ? AND code = ? AND expires_at > NOW() AND is_used = 0",
        [$userId, $code]
    );

    if (!$storedCode) {
        // Logo tentativën e dështuar
        logUserAction($userId, '2fa_failed', 'Kod 2FA i gabuar');

        echo json_encode(['success' => false, 'message' => 'Kodi është i gabuar ose ka skaduar.']);
        exit;
    }

    // Shëno kodin si të përdorur
    $db->update('two_factor_codes', ['is_used' => 1], 'id = ?', [$storedCode['id']]);

    // Fshi të gjitha kodet e vjetra për këtë përdorues
    $db->delete('two_factor_codes', 'user_id = ? AND id != ?', [$userId, $storedCode['id']]);

    // Merr të dhënat e përdoruesit
    $user = $db->fetchOne(
        "SELECT u.*, r.name as role_name FROM users u
         LEFT JOIN roles r ON u.role_id = r.id
         WHERE u.id = ?",
        [$userId]
    );

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Përdoruesi nuk u gjet.']);
        exit;
    }

    // Pastro tentativat e login
    $db->delete('login_attempts', 'email = ?', [$user['email']]);

    // Krijo sesionin
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['role_name'] ?? 'user';
    $_SESSION['last_activity'] = time();
    $_SESSION['email_verified'] = (bool) $user['email_verified'];

    // Pastro të dhënat 2FA nga sesioni
    unset($_SESSION['2fa_user_id'], $_SESSION['2fa_remember'], $_SESSION['2fa_expires'], $_SESSION['debug_2fa_code']);

    // Remember Me
    if ($remember) {
        refreshRememberToken($user['id']);
    }

    // Logo hyrjen
    logUserAction($user['id'], 'login_2fa', 'Hyrje e suksesshme me 2FA');

    // Përditëso last_login
    $db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

    // Kontrollo nëse ka redirect të ruajtur
    $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL;
    unset($_SESSION['redirect_after_login']);

    echo json_encode([
        'success' => true,
        'message' => 'Verifikimi u krye me sukses!',
        'redirect' => $redirect
    ]);
}

/**
 * Ridërgon kodin 2FA
 */
function resend2FACode($db, $userId) {
    // Kontrollo rate limiting (max 3 ridërgime në 10 minuta)
    $recentCodes = $db->fetchOne(
        "SELECT COUNT(*) as count FROM two_factor_codes
         WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)",
        [$userId]
    );

    if ($recentCodes && $recentCodes['count'] >= 3) {
        echo json_encode([
            'success' => false,
            'message' => 'Keni arritur limitin e ridërgimeve. Provoni përsëri pas disa minutash.'
        ]);
        exit;
    }

    // Merr email-in e përdoruesit
    $user = $db->fetchOne("SELECT email, first_name FROM users WHERE id = ?", [$userId]);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Përdoruesi nuk u gjet.']);
        exit;
    }

    // Gjenero kod të ri
    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

    // Fshi kodet e vjetra
    $db->delete('two_factor_codes', 'user_id = ?', [$userId]);

    // Ruaj kodin e ri
    $db->insert('two_factor_codes', [
        'user_id' => $userId,
        'code' => $code,
        'expires_at' => date('Y-m-d H:i:s', time() + 600)
    ]);

    // Përditëso sesionin
    $_SESSION['2fa_expires'] = time() + 600;

    // Dërgo email
    $emailSent = send2FAEmailResend($user['email'], $user['first_name'], $code);

    // Logo veprimin
    logUserAction($userId, '2fa_code_resent', 'Kod 2FA u ridërgua');

    $response = [
        'success' => true,
        'message' => 'Kodi i ri u dërgua në email-in tuaj.',
        'expires_at' => time() + 600
    ];

    // Për debug mode, kthe kodin
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        $response['debug_code'] = $code;
        $_SESSION['debug_2fa_code'] = $code;
    }

    echo json_encode($response);
}

/**
 * Dërgon email me kodin 2FA (ridërgim)
 */
function send2FAEmailResend($email, $firstName, $code) {
    $subject = SITE_NAME . ' - Kodi i Ri i Verifikimit';

    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .code { font-size: 32px; font-weight: bold; color: #0d6efd; letter-spacing: 5px;
                    background: #f8f9fa; padding: 20px; text-align: center; border-radius: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Përshëndetje, {$firstName}!</h2>
            <p>Kërkuat ridërgimin e kodit të verifikimit. Ja kodi juaj i ri:</p>
            <div class='code'>{$code}</div>
            <p><strong>Ky kod skadon pas 10 minutash.</strong></p>
            <hr>
            <p style='color: #6c757d; font-size: 12px;'>Ky email u dërgua automatikisht nga " . SITE_NAME . "</p>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <noreply@webplatform.com>\r\n";

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("2FA Resend Code for {$email}: {$code}");
        return true;
    }

    return @mail($email, $subject, $message, $headers);
}
